import { useTranslation } from "react-i18next";
import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";

export default function Header() {
  const [isOpen, setIsOpen] = useState(false);
  const { logout, isAuthenticated } = useAuth();

  const { t, i18n } = useTranslation();
  const navigate = useNavigate();

  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
  };

  const handleLogout = () => {
    logout();
    navigate("/");
  };

  return (
    <nav className="bg-gray-800 p-4">
      <div className="container mx-auto flex items-center justify-between flex-wrap">
        <div className="flex items-center flex-shrink-0 text-white mr-6">
          <Link to="/" className="font-semibold text-xl tracking-tight">
            Cloud Gallery
          </Link>
        </div>

        {/* Mobile menu button */}
        <div className="block lg:hidden">
          <button
            onClick={() => setIsOpen(!isOpen)}
            className="flex items-center px-3 py-2 border rounded text-gray-200 border-gray-400 hover:text-white hover:border-white"
          >
            <svg
              className="fill-current h-3 w-3"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <title>Menu</title>
              <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v15z" />
            </svg>
          </button>
        </div>

        {/* Menu items */}
        <div
          className={`${
            isOpen ? "block" : "hidden"
          } w-full flex-grow lg:flex lg:items-center lg:w-auto`}
        >
          <div className="text-sm lg:flex-grow"></div>
          <div className="mt-4 lg:mt-0 lg:ml-4 flex items-center">
            <div className="flex">
              {!isAuthenticated && (
                <>
                  <Link
                    to="/login"
                    className="block lg:inline-block text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-gray-800 hover:bg-white mr-2"
                  >
                    {t("sign_in")}
                  </Link>
                  <Link
                    to="/register"
                    className="block lg:inline-block text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-gray-800 hover:bg-white mr-2"
                  >
                    {t("sign_up")}
                  </Link>
                </>
              )}

              {isAuthenticated && (
                <>
                  <Link
                    to="/profile/edit"
                    className="block lg:inline-block text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-gray-800 hover:bg-white mr-2"
                  >
                    {t("profile")}
                  </Link>
                  <Link
                    to="/my-image-list"
                    className="block lg:inline-block text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-gray-800 hover:bg-white mr-2"
                  >
                    {t("my_image_list")}
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="block lg:inline-block text-sm px-4 py-2 leading-none border rounded text-white border-white hover:border-transparent hover:text-gray-800 hover:bg-white"
                  >
                    {t("logout")}
                  </button>
                </>
              )}
            </div>

            {/* Language selector */}
            <div className="relative inline-block text-left ml-4">
              <div>
                <button
                  type="button"
                  onClick={() => setIsOpen(!isOpen)}
                  className="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-gray-700 text-sm font-medium text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500"
                  id="menu-button"
                  aria-expanded="true"
                  aria-haspopup="true"
                >
                  {i18n.language.toUpperCase()}
                  <svg
                    className="-mr-1 ml-2 h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path
                      fillRule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clipRule="evenodd"
                    />
                  </svg>
                </button>
              </div>

              {isOpen && (
                <div
                  className="origin-top-right z-50 absolute right-0 mt-2 w-24 rounded-md shadow-lg bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none"
                  role="menu"
                  aria-orientation="vertical"
                  aria-labelledby="menu-button"
                  tabIndex="-1"
                >
                  <div className="py-1" role="none">
                    <button
                      onClick={() => {
                        changeLanguage("en");
                        setIsOpen(false);
                      }}
                      className="text-white block px-4 py-2 text-sm w-full text-left hover:bg-gray-600"
                      role="menuitem"
                      tabIndex="-1"
                      id="menu-item-0"
                    >
                      English
                    </button>
                    <button
                      onClick={() => {
                        changeLanguage("pt");
                        setIsOpen(false);
                      }}
                      className="text-white block px-4 py-2 text-sm w-full text-left hover:bg-gray-600"
                      role="menuitem"
                      tabIndex="-1"
                      id="menu-item-1"
                    >
                      Português
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </nav>
  );
}
