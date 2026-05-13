import { useCallback, useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import checkoutService from "../services/checkout-service";

export default function CheckoutSuccessPage() {
  const { t } = useTranslation("view-image-page");
  const [searchParams] = useSearchParams();
  const token = searchParams.get("token");

  const [downloadUrl, setDownloadUrl] = useState(null);
  const [filename, setFilename] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchDownload = useCallback(async () => {
    if (!token) {
      setError(t("checkout_download_expired"));
      setLoading(false);
      return;
    }

    try {
      const response = await checkoutService.getDownloadUrl({ token });
      setDownloadUrl(response.data.download_url);
      setFilename(response.data.filename);
    } catch {
      setError(t("checkout_download_expired"));
    } finally {
      setLoading(false);
    }
  }, [token, t]);

  useEffect(() => {
    fetchDownload();
  }, [fetchDownload]);

  return (
    <div className="flex justify-center p-4 sm:p-6 md:p-8 bg-gray-100 min-h-screen">
      <div className="w-full max-w-md bg-white rounded-lg shadow-xl p-8 text-center self-start mt-12">
        {loading ? (
          <div className="space-y-4">
            <div className="animate-spin mx-auto h-10 w-10 border-4 border-blue-600 border-t-transparent rounded-full"></div>
            <p className="text-gray-600">{t("checkout_loading")}</p>
          </div>
        ) : error ? (
          <div className="space-y-4">
            <div className="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100">
              <svg
                className="size-6 text-red-600"
                fill="none"
                viewBox="0 0 24 24"
                strokeWidth="1.5"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                />
              </svg>
            </div>
            <p className="text-gray-700">{error}</p>
            <Link
              to="/"
              className="inline-block mt-2 text-blue-600 hover:text-blue-500 font-medium"
            >
              {t("checkout_back_home")}
            </Link>
          </div>
        ) : (
          <div className="space-y-4">
            <div className="mx-auto flex size-12 items-center justify-center rounded-full bg-green-100">
              <svg
                className="size-6 text-green-600"
                fill="none"
                viewBox="0 0 24 24"
                strokeWidth="1.5"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  d="m4.5 12.75 6 6 9-13.5"
                />
              </svg>
            </div>

            <h2 className="text-xl font-semibold text-gray-900">
              {t("checkout_success_title")}
            </h2>
            <p className="text-sm text-gray-600">
              {t("checkout_success_description")}
            </p>

            <a
              href={downloadUrl}
              download={filename}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors"
            >
              {t("checkout_download_button")}
            </a>

            <Link
              to="/"
              className="inline-block mt-2 text-blue-600 hover:text-blue-500 font-medium text-sm"
            >
              {t("checkout_back_home")}
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
