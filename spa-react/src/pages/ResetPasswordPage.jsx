import { useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { toast } from "react-toastify";
import { useTranslation } from "react-i18next";
import authService from "../services/auth-service";
import PasswordInput from "../components/PasswordInput";

export default function ResetPasswordPage() {
  const { t } = useTranslation("reset-password-page");
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const token = searchParams.get("token") ?? "";
  const emailFromLink = searchParams.get("email") ?? "";

  const [formData, setFormData] = useState({
    email: emailFromLink,
    password: "",
    password_confirmation: "",
  });

  const [errors, setErrors] = useState({
    password: "",
    password_confirmation: "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const validateForm = () => {
    const newErrors = { password: "", password_confirmation: "" };
    let isValid = true;

    if (formData.password.length < 8) {
      newErrors.password = t("validation.passwordMinLength");
      isValid = false;
    }

    if (formData.password_confirmation !== formData.password) {
      newErrors.password_confirmation = t("validation.passwordsMismatch");
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!token) {
      toast(t("invalidLinkMessage"));
      return;
    }
    if (!validateForm()) return;

    setIsSubmitting(true);

    try {
      await authService.resetPassword({
        token,
        email: formData.email,
        password: formData.password,
        passwordConfirmation: formData.password_confirmation,
      });
      toast(t("successMessage"));
      navigate("/login");
    } catch (error) {
      const message = error.response?.data?.message ?? t("errorMessage");
      toast(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100 p-4">
      <div className="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h1 className="text-3xl font-bold text-gray-800 mb-6 text-center">
          {t("title")}
        </h1>
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
              className="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 bg-gray-50"
              value={formData.email}
              readOnly
            />
          </div>

          <div>
            <label
              htmlFor="password"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {t("passwordLabel")}
            </label>
            <PasswordInput
              id="password"
              name="password"
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                ${errors.password ? "border-red-500" : "border-gray-300"}`}
              autoComplete="new-password"
              value={formData.password}
              onChange={handleChange}
            />
            {errors.password && (
              <p className="mt-2 text-sm text-red-600">{errors.password}</p>
            )}
          </div>

          <div>
            <label
              htmlFor="password_confirmation"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {t("passwordConfirmationLabel")}
            </label>
            <PasswordInput
              id="password_confirmation"
              name="password_confirmation"
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                ${errors.password_confirmation ? "border-red-500" : "border-gray-300"}`}
              autoComplete="new-password"
              value={formData.password_confirmation}
              onChange={handleChange}
            />
            {errors.password_confirmation && (
              <p className="mt-2 text-sm text-red-600">
                {errors.password_confirmation}
              </p>
            )}
          </div>

          <button
            type="submit"
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isSubmitting}
          >
            {isSubmitting ? t("buttonResetting") : t("buttonReset")}
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
