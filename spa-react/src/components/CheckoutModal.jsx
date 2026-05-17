import {
  Dialog,
  DialogBackdrop,
  DialogPanel,
  DialogTitle,
} from "@headlessui/react";
import { ShoppingCartIcon } from "@heroicons/react/24/outline";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { useAuth } from "../contexts/AuthContext";
import checkoutService from "../services/checkout-service";
import LoadingButton from "./LoadingButton";

export function CheckoutModal({ isOpen, onClose, image, priceCents }) {
  const { t } = useTranslation("view-image-page");
  const { t: tc } = useTranslation("common");
  const { user, isAuthenticated } = useAuth();

  const [email, setEmail] = useState("");
  const [error, setError] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  const formattedPrice = (priceCents / 100).toFixed(2);

  const handleCheckout = async () => {
    setError(null);

    if (!isAuthenticated && !email) {
      setError(t("checkout_email_required"));
      return;
    }

    setIsLoading(true);

    try {
      const response = await checkoutService.createSession({
        imageId: image.id,
        email: isAuthenticated ? null : email,
      });

      const { checkout_url } = response.data;
      window.location.href = checkout_url;
    } catch (err) {
      const message =
        err.response?.data?.message || t("checkout_error");
      setError(message);
      setIsLoading(false);
    }
  };

  const handleClose = () => {
    if (!isLoading) {
      setError(null);
      setEmail("");
      onClose(false);
    }
  };

  return (
    <Dialog open={isOpen} onClose={handleClose} className="relative z-50">
      <DialogBackdrop
        transition
        className="fixed inset-0 bg-gray-500/75 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"
      />

      <div className="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <DialogPanel
            transition
            className="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 sm:w-full sm:max-w-lg data-closed:sm:translate-y-0 data-closed:sm:scale-95"
          >
            <div className="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div className="sm:flex sm:items-start">
                <div className="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:size-10">
                  <ShoppingCartIcon
                    aria-hidden="true"
                    className="size-6 text-blue-600"
                  />
                </div>
                <div className="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <DialogTitle
                    as="h3"
                    className="text-base font-semibold text-gray-900"
                  >
                    {t("checkout_title")}
                  </DialogTitle>

                  <div className="mt-4 space-y-3">
                    <div className="flex justify-between items-center bg-gray-50 p-3 rounded-md">
                      <span className="text-sm text-gray-600">
                        {image?.title}
                      </span>
                      <span className="text-lg font-semibold text-gray-900">
                        ${formattedPrice}
                      </span>
                    </div>

                    <p className="text-sm text-gray-500">
                      {t("checkout_description")}
                    </p>

                    {isAuthenticated ? (
                      <p className="text-sm text-gray-600">
                        {t("checkout_logged_as", { email: user?.data?.email })}
                      </p>
                    ) : (
                      <div>
                        <label
                          htmlFor="checkout-email"
                          className="block text-sm font-medium text-gray-700 mb-1"
                        >
                          {t("checkout_email_label")}
                        </label>
                        <input
                          id="checkout-email"
                          type="email"
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          placeholder={t("checkout_email_placeholder")}
                          className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        />
                      </div>
                    )}

                    {error && (
                      <p className="text-sm text-red-600">{error}</p>
                    )}
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
              <LoadingButton
                onClick={handleCheckout}
                isLoading={isLoading}
                loadingText=""
                className="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold
                cursor-pointer
                text-white shadow-xs hover:bg-blue-500 sm:ml-3 sm:w-auto"
              >
                {t("checkout_pay_button")}
              </LoadingButton>

              <button
                type="button"
                data-autofocus
                onClick={handleClose}
                disabled={isLoading}
                className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs
                cursor-pointer
                inset-ring inset-ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
              >
                {tc("cancel")}
              </button>
            </div>
          </DialogPanel>
        </div>
      </div>
    </Dialog>
  );
}
