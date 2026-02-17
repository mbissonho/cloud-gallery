import React from "react";
import { useTranslation } from "react-i18next";

export function EditButton({ image, handleEdit }) {
  const { t: ti } = useTranslation("image-grid");

  return (
    <button
      type="button"
      onClick={(event) => {
        event.stopPropagation();
        handleEdit(image);
      }}
      className="mr-2 inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
    >
      {ti("edit_button")}
    </button>
  );
}
