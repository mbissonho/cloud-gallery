import React, { memo, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import placeholderImage from "../assets/placeholder.png";
import ImageLoader from "./ImageLoader";

const ImageGridComponent = ({ imageList, children }) => {
  const navigate = useNavigate();
  const { t } = useTranslation();
  const { t: ti } = useTranslation("image-grid");

  const commonComponents = ["StatusBadge"];

  const handlePageChange = useCallback(
    (newPage) => {
      imageList.setPagination((prev) => ({ ...prev, page: newPage }));
    },
    [imageList]
  );

  const handleImageClick = useCallback(
    (image) => {
      if (image.status === "PROCESSING") return;
      navigate(`/view/${image.id}`, { state: image });
    },
    [navigate]
  );

  const getComponentName = (child) => {
    return child.type.displayName || child.type.name;
  };

  if (imageList.loading) {
    return (
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
        {Array.from({ length: 4 }).map((_, index) => (
          <div
            key={index}
            className="mb-4 animate-pulse"
            style={{
              animationDelay: `${index * 150}ms`,
              animationDuration: "2s",
            }}
          >
            <div className="relative flex flex-col h-full bg-white border border-gray-100 rounded-lg shadow-sm">
              <div className="w-full h-48 bg-gray-200 rounded-t-lg"></div>

              <div className="flex-grow p-4 flex flex-col justify-between">
                <div>
                  <div
                    className="h-5 bg-gray-200 rounded mb-2"
                    style={{ width: `${Math.random() * (85 - 60) + 60}%` }}
                  ></div>

                  {/* Data */}
                  <div className="h-3 bg-gray-200 rounded w-1/3 mt-2"></div>
                </div>

                {/* Tags */}
                <div className="flex flex-wrap gap-1 mt-4 mb-2">
                  <div className="h-5 w-12 bg-gray-200 rounded-full"></div>
                  <div className="h-5 w-16 bg-gray-200 rounded-full"></div>
                </div>

                {/* Buttons */}
                <div className="flex items-center mt-auto gap-2">
                  <div className="h-8 w-8 bg-gray-100 rounded-md border border-gray-200"></div>
                  <div className="h-8 w-8 bg-gray-100 rounded-md border border-gray-200"></div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  if (imageList.error)
    return (
      <div className="container mx-auto mt-4">
        {ti("error")} {imageList.error.message}
      </div>
    );

  return (
    <>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
        {imageList.items.map((image) => (
          <div
            key={image.id}
            className="mb-4"
            onClick={() => handleImageClick(image)}
          >
            <div className="relative flex flex-col h-full bg-white border border-gray-200 rounded-lg shadow-sm cursor-pointer hover:shadow-md transition-shadow duration-200">
              <ImageLoader
                src={image.thumbnail_url}
                className={"w-full h-48 object-cover rounded-t-lg"}
                placeholderSrc={placeholderImage}
                altText={image.title}
                image={image}
              />
              <div className="flex-grow p-4 flex flex-col justify-between">
                <div>
                  <span className="text-lg font-bold text-gray-800 line-clamp-2">
                    {image.title}
                  </span>
                  <small className="text-gray-500 text-sm mt-1 block">
                    {image.created_at}
                  </small>
                </div>

                <div className="flex flex-wrap gap-1 mt-2 mb-2">
                  {image.tag_names &&
                    image.tag_names.map((tagName, tagIndex) => (
                      <span
                        key={`${image.id}-tag-${tagIndex}`}
                        className="px-2 py-0.5 text-xs font-semibold bg-gray-200 text-gray-700 rounded-full"
                      >
                        {tagName}
                      </span>
                    ))}
                </div>

                <div className="flex items-center mt-auto">
                  {React.Children.map(children, (child) => {
                    if (React.isValidElement(child)) {
                      const childName = getComponentName(child);

                      if (
                        childName &&
                        commonComponents.includes(childName) &&
                        image?.status === "PROCESSING"
                      ) {
                        return React.cloneElement(child, { image });
                      } else if (
                        image?.status &&
                        image.status !== "PROCESSING"
                      ) {
                        return React.cloneElement(child, { image });
                      } else if (image?.status === "PROCESSING") {
                        return null;
                      }

                      return null;
                    }
                    return null;
                  })}
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
      {imageList.items?.length >= 1 && (
        <div className="flex justify-between items-center my-6">
          <button
            className="px-4 py-2 cursor-pointer border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={imageList.pagination.page === 1}
            onClick={() => handlePageChange(imageList.pagination.page - 1)}
          >
            {t("page_previous")}
          </button>
          <span className="text-gray-700 text-sm">
            {" "}
            {t("page_of", {
              current: imageList.pagination.page,
              total: imageList.pagination.totalPages,
            })}{" "}
          </span>
          <button
            className="px-4 py-2 border cursor-pointer border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            disabled={
              imageList.pagination.page === imageList.pagination.totalPages
            }
            onClick={() => handlePageChange(imageList.pagination.page + 1)}
          >
            {t("page_next")}
          </button>
        </div>
      )}
    </>
  );
};

export const ImageGrid = memo(ImageGridComponent);
