import axios from "axios";
import i18n from "../i18n";

const axiosClient = axios.create({
  baseURL: import.meta.env.VITE_BACKEND_BASE_URL,
  withCredentials: true,
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
  maxRedirects: 0,
});

const getCookie = (name) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
};

// The backend Locale middleware only recognises 'en' and 'pt_BR'.
// i18next reports current language as 'pt', 'pt-BR', 'en', 'en-US', etc.,
// so map first to avoid the middleware silently falling back to the default.
const FRONTEND_TO_BACKEND_LOCALE = {
  en: "en",
  pt: "pt_BR",
  "pt-BR": "pt_BR",
};

const resolveBackendLocale = () => {
  const current = i18n.language ?? "en";
  return (
    FRONTEND_TO_BACKEND_LOCALE[current] ??
    FRONTEND_TO_BACKEND_LOCALE[current.split("-")[0]] ??
    "en"
  );
};

axiosClient.interceptors.request.use(
  (config) => {
    const token = getCookie("XSRF-TOKEN");
    if (token) {
      config.headers["X-XSRF-TOKEN"] = decodeURIComponent(token);
    }

    config.headers["lang"] = resolveBackendLocale();

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default axiosClient;
