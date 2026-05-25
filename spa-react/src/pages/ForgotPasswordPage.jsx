import { useState } from "react";
import { Link } from "react-router-dom";
import { toast } from "react-toastify";
import { useTranslation } from "react-i18next";
import validateEmail from "../validators/validate-email";
import authService from "../services/auth-service";

export default function ForgotPasswordPage() {
  const { t } = useTranslation("forgot-password-page");

  const [email, setEmail] = useState("");
  const [emailError, setEmailError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateEmail(email)) {
      setEmailError(t("validation.invalidEmail"));
      return;
    }
    setEmailError("");
    setIsSubmitting(true);

    try {
      await authService.requestPasswordReset({ email });
      toast(t("successMessage"));
    } catch (error) {
      toast(t("errorMessage"));
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100 p-4">
      <div className="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h1 className="text-3xl font-bold text-gray-800 mb-2 text-center">
          {t("title")}
        </h1>
        <p className="text-sm text-gray-600 mb-6 text-center">
          {t("description")}
        </p>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label
              htmlFor="email"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {t("emailLabel")}
            </label>
            <input
              type="email"
              id="email"
              name="email"
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                ${emailError ? "border-red-500" : "border-gray-300"}`}
              autoComplete="off"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            {emailError && (
              <p className="mt-2 text-sm text-red-600">{emailError}</p>
            )}
          </div>

          <button
            type="submit"
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isSubmitting}
          >
            {isSubmitting ? t("buttonSending") : t("buttonSend")}
          </button>

          <div className="text-center text-sm">
            <Link to="/login" className="text-blue-600 hover:underline">
              {t("backToLogin")}
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
