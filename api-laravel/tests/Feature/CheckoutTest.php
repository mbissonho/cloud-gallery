<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentGatewayException;
use App\Mail\DownloadLinkMail;
use App\Models\Image;
use App\Models\Purchase;
use App\Models\PurchaseStatus;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    private MockInterface $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        // Replace the real Stripe gateway with a mock so tests never reach the network.
        $this->gateway = $this->mock(PaymentGatewayInterface::class);
        $this->gateway->shouldReceive('getGatewayName')->andReturn('stripe')->byDefault();
    }

    private function availableImage(int $priceCents = 0): Image
    {
        return Image::factory()
            ->available()
            ->ofUser(User::factory()->create()->id)
            ->create(['price_cents' => $priceCents]);
    }

    private function makePendingPurchase(string $sessionId, ?int $userId = null): Purchase
    {
        return Purchase::create([
            'image_id' => $this->availableImage()->id,
            'user_id' => $userId,
            'buyer_email' => 'buyer@example.com',
            'amount_cents' => 500,
            'currency' => 'usd',
            'gateway' => 'stripe',
            'gateway_session_id' => $sessionId,
            'download_token' => Purchase::generateDownloadToken(),
            'status' => PurchaseStatus::PENDING,
        ]);
    }

    public function test_create_session_validation_fails_when_image_id_is_missing(): void
    {
        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), ['email' => 'guest@example.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image_id']);
    }

    public function test_create_session_validation_fails_when_image_does_not_exist(): void
    {
        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), [
                'image_id' => 99999,
                'email' => 'guest@example.com',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image_id']);
    }

    public function test_create_session_returns_404_for_processing_image(): void
    {
        //Arrange
        $processing = Image::factory()
            ->processing()
            ->ofUser(User::factory()->create()->id)
            ->create();

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), [
                'image_id' => $processing->id,
                'email' => 'guest@example.com',
            ])
            ->assertNotFound();
    }

    public function test_create_session_returns_404_for_disabled_image(): void
    {
        //Arrange
        $disabled = Image::factory()
            ->disabled()
            ->ofUser(User::factory()->create()->id)
            ->create();

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), [
                'image_id' => $disabled->id,
                'email' => 'guest@example.com',
            ])
            ->assertNotFound();
    }

    public function test_create_session_returns_422_when_guest_omits_email(): void
    {
        //Arrange
        $image = $this->availableImage();

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), ['image_id' => $image->id])
            ->assertStatus(422)
            ->assertJson(['message' => trans('checkout.guest_email_required')]);
    }

    public function test_create_session_for_guest_creates_pending_purchase_and_returns_checkout_url(): void
    {
        //Arrange
        $image = $this->availableImage(priceCents: 1500);
        $checkoutUrl = 'https://stripe.test/checkout/abc';

        $this->gateway
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn(['session_id' => 'cs_test_guest', 'checkout_url' => $checkoutUrl]);

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), [
                'image_id' => $image->id,
                'email' => 'guest@example.com',
            ])
            ->assertOk()
            ->assertJson(['checkout_url' => $checkoutUrl]);

        $this->assertDatabaseHas('purchases', [
            'image_id' => $image->id,
            'user_id' => null,
            'buyer_email' => 'guest@example.com',
            'amount_cents' => 1500,
            'gateway' => 'stripe',
            'gateway_session_id' => 'cs_test_guest',
            'status' => PurchaseStatus::PENDING->value,
        ]);
    }

    public function test_create_session_uses_authenticated_user_email_and_links_purchase_to_user(): void
    {
        //Arrange
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $image = $this->availableImage();

        $this->gateway
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->with(Mockery::on(fn (array $p) => $p['customer_email'] === 'owner@example.com'))
            ->andReturn(['session_id' => 'cs_test_user', 'checkout_url' => 'https://stripe.test/x']);

        //Act and Assert
        $this
            ->actingAs($user)
            ->postJson(route('api.v1.checkout.session'), ['image_id' => $image->id])
            ->assertOk();

        $this->assertDatabaseHas('purchases', [
            'image_id' => $image->id,
            'user_id' => $user->id,
            'buyer_email' => 'owner@example.com',
            'gateway_session_id' => 'cs_test_user',
        ]);
    }

    public function test_create_session_falls_back_to_default_price_when_image_has_no_price(): void
    {
        //Arrange
        config()->set('checkout.default_price_cents', 750);
        $image = $this->availableImage(priceCents: 0);

        $this->gateway
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->with(Mockery::on(fn (array $p) => $p['amount_cents'] === 750))
            ->andReturn(['session_id' => 'cs_test_default', 'checkout_url' => 'https://stripe.test/x']);

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.session'), [
                'image_id' => $image->id,
                'email' => 'guest@example.com',
            ])
            ->assertOk();

        $this->assertDatabaseHas('purchases', [
            'gateway_session_id' => 'cs_test_default',
            'amount_cents' => 750,
        ]);
    }

    public function test_create_session_returns_503_when_gateway_throws_and_does_not_leak_provider_message(): void
    {
        //Arrange
        $image = $this->availableImage(priceCents: 500);
        $rawStripeMessage = 'Invalid API Key provided: CHANGE_ME';

        $this->gateway
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->andThrow(new PaymentGatewayException(
                'Failed to create Stripe checkout session: ' . $rawStripeMessage
            ));

        //Act and Assert
        $response = $this
            ->postJson(route('api.v1.checkout.session'), [
                'image_id' => $image->id,
                'email' => 'guest@example.com',
            ])
            ->assertStatus(503)
            ->assertJson(['message' => trans('checkout.gateway_unavailable')]);

        // Provider-specific detail must never reach the frontend.
        $body = $response->getContent();
        $this->assertStringNotContainsString('CHANGE_ME', $body);
        $this->assertStringNotContainsString('API Key', $body);
        $this->assertStringNotContainsString('Stripe', $body);

        // The session call failed, so no Purchase row should have been written.
        $this->assertDatabaseMissing('purchases', ['image_id' => $image->id]);
    }

    public function test_create_session_returns_403_when_authenticated_user_owns_the_image(): void
    {
        //Arrange
        $owner = User::factory()->create();
        $image = Image::factory()
            ->available()
            ->ofUser($owner->id)
            ->create(['price_cents' => 500]);

        // Owner check happens before gateway invocation — the gateway must not be touched.
        $this->gateway->shouldNotReceive('createCheckoutSession');

        //Act and Assert
        $this
            ->actingAs($owner)
            ->postJson(route('api.v1.checkout.session'), ['image_id' => $image->id])
            ->assertStatus(403)
            ->assertJson(['message' => trans('checkout.cannot_buy_own_image')]);

        $this->assertDatabaseMissing('purchases', ['image_id' => $image->id]);
    }

    public function test_webhook_with_invalid_signature_returns_400(): void
    {
        //Arrange
        $this->gateway
            ->shouldReceive('parseWebhookPayload')
            ->once()
            ->andReturn(null);

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.webhook'), ['fake' => 'payload'])
            ->assertStatus(400)
            ->assertJson(['message' => trans('checkout.invalid_webhook')]);
    }

    public function test_webhook_completes_pending_purchase_and_queues_download_email(): void
    {
        //Arrange
        Mail::fake();
        $purchase = $this->makePendingPurchase('cs_test_webhook');

        $this->gateway
            ->shouldReceive('parseWebhookPayload')
            ->once()
            ->andReturn([
                'event_type' => 'checkout.completed',
                'session_id' => 'cs_test_webhook',
                'payment_id' => 'pi_test_123',
            ]);

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.webhook'), [])
            ->assertOk();

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => PurchaseStatus::COMPLETED->value,
            'gateway_payment_id' => 'pi_test_123',
        ]);

        Mail::assertQueued(
            DownloadLinkMail::class,
            fn (DownloadLinkMail $mail) => $mail->hasTo($purchase->buyer_email)
        );
    }

    public function test_webhook_is_idempotent_when_purchase_already_completed(): void
    {
        //Arrange
        Mail::fake();
        $purchase = $this->makePendingPurchase('cs_test_idempotent');
        $purchase->update([
            'status' => PurchaseStatus::COMPLETED,
            'gateway_payment_id' => 'pi_first_payment',
            'download_expires_at' => now()->addHours(48),
        ]);

        $this->gateway
            ->shouldReceive('parseWebhookPayload')
            ->once()
            ->andReturn([
                'event_type' => 'checkout.completed',
                'session_id' => 'cs_test_idempotent',
                'payment_id' => 'pi_second_payment',
            ]);

        //Act and Assert
        $this
            ->postJson(route('api.v1.checkout.webhook'), [])
            ->assertOk();

        // The first payment id must not be overwritten by the second webhook delivery.
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'gateway_payment_id' => 'pi_first_payment',
        ]);

        Mail::assertNothingQueued();
    }

    public function test_download_with_unknown_token_returns_404(): void
    {
        //Act and Assert
        $this
            ->getJson(route('api.v1.checkout.download', ['token' => 'nonexistent-token']))
            ->assertNotFound();
    }

    public function test_download_returns_403_when_pending_purchase_is_not_paid_at_gateway(): void
    {
        //Arrange
        $purchase = $this->makePendingPurchase('cs_pending_unpaid');

        $this->gateway
            ->shouldReceive('verifySessionPayment')
            ->once()
            ->with('cs_pending_unpaid')
            ->andReturn(['paid' => false, 'payment_id' => null]);

        //Act and Assert
        $this
            ->getJson(route('api.v1.checkout.download', ['token' => $purchase->download_token]))
            ->assertStatus(403)
            ->assertJson(['message' => trans('checkout.download_unavailable')]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => PurchaseStatus::PENDING->value,
        ]);
    }

    public function test_download_falls_forward_and_completes_pending_purchase_when_gateway_confirms_payment(): void
    {
        //Arrange
        Mail::fake();

        $temporaryUrl = 'https://s3.test/presigned-fall-forward';
        Storage::fake('main-image');
        $proxy = Mockery::mock(Storage::disk('main-image'));
        $proxy->shouldReceive('temporaryUrl')->once()->andReturn($temporaryUrl);
        Storage::set('main-image', $proxy);

        $purchase = $this->makePendingPurchase('cs_pending_paid');

        $this->gateway
            ->shouldReceive('verifySessionPayment')
            ->once()
            ->with('cs_pending_paid')
            ->andReturn(['paid' => true, 'payment_id' => 'pi_paid_now']);

        //Act and Assert
        $this
            ->getJson(route('api.v1.checkout.download', ['token' => $purchase->download_token]))
            ->assertOk()
            ->assertJsonStructure(['download_url', 'filename', 'expires_in_minutes'])
            ->assertJson([
                'download_url' => $temporaryUrl,
                'expires_in_minutes' => 15,
            ]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => PurchaseStatus::COMPLETED->value,
            'gateway_payment_id' => 'pi_paid_now',
        ]);

        Mail::assertQueued(DownloadLinkMail::class);
    }

    public function test_download_returns_presigned_url_for_completed_purchase_without_calling_gateway(): void
    {
        //Arrange
        $temporaryUrl = 'https://s3.test/presigned-completed';
        Storage::fake('main-image');
        $proxy = Mockery::mock(Storage::disk('main-image'));
        $proxy->shouldReceive('temporaryUrl')->once()->andReturn($temporaryUrl);
        Storage::set('main-image', $proxy);

        $purchase = $this->makePendingPurchase('cs_already_completed');
        $purchase->update([
            'status' => PurchaseStatus::COMPLETED,
            'download_expires_at' => now()->addHour(),
        ]);

        // Status is already COMPLETED, so the controller must not hit the gateway again.
        $this->gateway->shouldNotReceive('verifySessionPayment');

        //Act and Assert
        $this
            ->getJson(route('api.v1.checkout.download', ['token' => $purchase->download_token]))
            ->assertOk()
            ->assertJson([
                'download_url' => $temporaryUrl,
                'expires_in_minutes' => 15,
            ]);
    }

    public function test_download_returns_403_when_completed_purchase_link_has_expired(): void
    {
        //Arrange
        $purchase = $this->makePendingPurchase('cs_expired');
        $purchase->update([
            'status' => PurchaseStatus::COMPLETED,
            'download_expires_at' => now()->subHour(),
        ]);

        $this->gateway->shouldNotReceive('verifySessionPayment');

        //Act and Assert
        $this
            ->getJson(route('api.v1.checkout.download', ['token' => $purchase->download_token]))
            ->assertStatus(403)
            ->assertJson(['message' => trans('checkout.download_unavailable')]);
    }
}
