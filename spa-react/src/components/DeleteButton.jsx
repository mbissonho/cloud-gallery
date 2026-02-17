import React from "react";
import { useTranslation } from "react-i18next";

export function DeleteButton({ image, handleDelete }) {
  const { t: ti } = useTranslation("image-grid");

  return (
    <button
      type="button"
      onClick={(event) => {
        event.stopPropagation();
        handleDelete(image);
      }}
      className="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-2"
    >
      {ti("delete_button")}
    </button>
  );
}
