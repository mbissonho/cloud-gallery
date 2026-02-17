import { S3Client, GetObjectCommand, PutObjectCommand } from "@aws-sdk/client-s3";
import sharp from "sharp";

const s3Client = new S3Client({});

const DEFAULT_THUMBNAIL_WIDTH = 200;
const DEFAULT_THUMBNAIL_HEIGHT = 200;
const DEFAULT_THUMBNAIL_QUALITY = 80;

export const handler = async (event) => {
    console.log('LAMBDA: Received event from SQS!');

    for (const record of event.Records) {
        try {

            if (!record.body) {
                console.warn(`LAMBDA WARN: Record with messageId ${record.messageId} has no body. Skipping.`);
                continue;
            }

            const sqsMessageBody = JSON.parse(record.body);
            if (!sqsMessageBody.Message) {
                console.warn(`LAMBDA WARN: SQS message body does not contain a 'Message' property. Skipping. Body: ${record.body}`);
                continue;
            }

            const snsMessage = JSON.parse(sqsMessageBody.Message);
            if (!snsMessage.Records || snsMessage.Records.length === 0) {
                console.warn(`LAMBDA WARN: SNS message does not contain 'Records'. Skipping. Message: ${sqsMessageBody.Message}`);
                continue;
            }

            const s3Record = snsMessage.Records[0];

            const sourceBucket = s3Record.s3.bucket.name;
            const sourceKey = decodeURIComponent(s3Record.s3.object.key.replace(/\+/g, ' '));

            console.log(`LAMBDA: Processing file from bucket: [${sourceBucket}], key: [${sourceKey}]`);

            const getObjectParams = {
                Bucket: sourceBucket,
                Key: sourceKey,
            };
            const getObjectResponse = await s3Client.send(new GetObjectCommand(getObjectParams));
            const originalImageBuffer = await getObjectResponse.Body.transformToByteArray();

            console.log(`LAMBDA: Downloaded original image, size: ${originalImageBuffer.length} bytes.`);

            const thumbnailWidth = parseInt(process.env.THUMBNAIL_WIDTH ?? DEFAULT_THUMBNAIL_WIDTH);
            const thumbnailHeight = parseInt(process.env.THUMBNAIL_HEIGHT ?? DEFAULT_THUMBNAIL_HEIGHT);
            const thumbnailQuality = parseInt(process.env.THUMBNAIL_QUALITY ?? DEFAULT_THUMBNAIL_QUALITY);

            const thumbnailBuffer = await sharp(originalImageBuffer)
                .resize({
                    width: thumbnailWidth,
                    height: thumbnailHeight,
                    fit: 'inside',
                    withoutEnlargement: true,
                })
                .jpeg({ quality: thumbnailQuality })
                .toBuffer();

            console.log(`LAMBDA: Created thumbnail, size: ${thumbnailBuffer.length} bytes.`);

            const thumbnailBucket = process.env.THUMBNAIL_BUCKET_NAME;
            if (!thumbnailBucket) {
                throw new Error("Environment variable THUMBNAIL_BUCKET_NAME is not defined.");
            }

            const destinationKey = sourceKey;

            const putObjectParams = {
                Bucket: thumbnailBucket,
                Key: destinationKey,
                Body: thumbnailBuffer,
                ContentType: 'image/jpeg',
            };
            await s3Client.send(new PutObjectCommand(putObjectParams));

            console.log(`LAMBDA SUCCESS: Uploaded thumbnail to bucket: [${thumbnailBucket}], key: [${destinationKey}]`);

        } catch (error) {
            console.error('LAMBDA CRITICAL: Failed to process S3 record.', error);
            throw error;
        }
    }

    return {
        statusCode: 200,
        body: JSON.stringify('Processed all records successfully.'),
    };
};