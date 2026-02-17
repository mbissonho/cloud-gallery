import { useState } from "react";
import { useNavigate } from "react-router-dom";
import validateEmail from "../validators/validate-email";
import { toast } from "react-toastify";
import { useTranslation } from "react-i18next";
import { useAuth } from "../contexts/AuthContext";

export default function Login() {
  const navigate = useNavigate();

  const { login } = useAuth();
  const { t } = useTranslation("login-page");
  const [formData, setFormData] = useState({
    email: "john.doe@mail.com",
    password: "password",
  });

  const [errors, setErrors] = useState({ email: "", password: "" });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const validateForm = () => {
    let newErrors = { email: "", password: "" };
    let isValid = true;

    if (!validateEmail(formData.email)) {
      newErrors.email = t("validation.invalidEmail");
      isValid = false;
    }

    if (!formData.password) {
      newErrors.password = t("validation.passwordRequired");
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
    if (!validateForm()) return;

    setIsSubmitting(true);

    try {
      await login({ email: formData.email, password: formData.password });

      navigate("/");
    } catch (error) {
      toast(t("errorMessage"));
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
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                ${
                                  errors.email
                                    ? "border-red-500"
                                    : "border-gray-300"
                                }`}
              autoComplete="off"
              value={formData.email}
              onChange={handleChange}
            />
            {errors.email && (
              <p className="mt-2 text-sm text-red-600">{errors.email}</p>
            )}
          </div>

          <div>
            <label
              htmlFor="password"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {t("passwordLabel")}
            </label>
            <input
              type="password"
              id="password"
              name="password"
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                ${
                                  errors.password
                                    ? "border-red-500"
                                    : "border-gray-300"
                                }`}
              autoComplete="off"
              value={formData.password}
              onChange={handleChange}
            />
            {errors.password && (
              <p className="mt-2 text-sm text-red-600">{errors.password}</p>
            )}
          </div>

          <button
            type="submit"
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isSubmitting}
          >
            {isSubmitting ? t("buttonLoggingIn") : t("buttonLogin")}
          </button>
        </form>
      </div>
    </div>
  );
}
