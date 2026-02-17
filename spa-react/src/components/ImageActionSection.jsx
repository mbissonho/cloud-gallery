import { memo, useCallback } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";

const ImageActionSectionComponent = ({ items }) => {
  const { t: til } = useTranslation("image-action-section");
  const navigate = useNavigate();

  const handleRedirectToCreate = useCallback(() => {
    navigate("/new");
  }, [navigate]);

  if (!items || items.length === 0) {
    return (
      <div
        className="col-span-12 my-4 cursor-pointer rounded-lg border-2 border-dashed border-gray-400 p-6 text-center text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors duration-200"
        onClick={handleRedirectToCreate}
      >
        <p className="text-lg">{til("no-images-yet")}</p>
      </div>
    );
  }

  return (
    <div className="my-4 text-right">
      <button
        onClick={handleRedirectToCreate}
        className="rounded-md bg-blue-600 px-4 py-2 text-white shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75 transition-colors duration-200"
      >
        {til("add-new-image")}
      </button>
    </div>
  );
};

export const ImageActionSection = memo(ImageActionSectionComponent);
