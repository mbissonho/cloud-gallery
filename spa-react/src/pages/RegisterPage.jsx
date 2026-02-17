import { useState } from "react";
import { useNavigate } from "react-router-dom";
import validateEmail from "../validators/validate-email";
import { toast } from "react-toastify";
import { useTranslation } from "react-i18next";

export default function Register() {
  const navigate = useNavigate();
  const { t } = useTranslation("register-page");

  const [formData, setFormData] = useState({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });

  const [errors, setErrors] = useState({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const validateForm = () => {
    let newErrors = {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
    };
    let isValid = true;

    if (formData.name.length < 3) {
      newErrors.name = t("validation.nameMinLength");
      isValid = false;
    }

    if (!validateEmail(formData.email)) {
      newErrors.email = t("validation.invalidEmail");
      isValid = false;
    }

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
    if (!validateForm()) return;

    setIsSubmitting(true);

    try {
      const response = await fetch(
        `${import.meta.env.VITE_BACKEND_BASE_URL}/api/v1/auth/register`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(formData),
        }
      );

      if (response.ok) {
        toast(t("successMessage"));
        navigate("/login");
      } else {
        const data = response.data;
        toast(data?.message ?? t("errorMessage"));
      }
    } catch (error) {
      console.error(t("requestError"), error);
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
              htmlFor="name"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {t("nameLabel")}
            </label>
            <input
              type="text"
              id="name"
              name="name"
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                ${
                                  errors.name
                                    ? "border-red-500"
                                    : "border-gray-300"
                                }`}
              autoComplete="off"
              value={formData.name}
              onChange={handleChange}
            />
            {errors.name && (
              <p className="mt-2 text-sm text-red-600">{errors.name}</p>
            )}
          </div>

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

          <div>
            <label
              htmlFor="password_confirmation"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              {t("passwordConfirmationLabel")}
            </label>
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                                ${
                                  errors.password_confirmation
                                    ? "border-red-500"
                                    : "border-gray-300"
                                }`}
              autoComplete="off"
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
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={isSubmitting}
          >
            {isSubmitting ? t("buttonRegistering") : t("buttonRegister")}
          </button>
        </form>
      </div>
    </div>
  );
}
