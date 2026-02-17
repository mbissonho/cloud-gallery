import { memo, useRef, useState } from "react";
import { useTranslation } from "react-i18next";
import { TagSearchAndSelectInput } from "./TagSearchAndSelectInput";

const ImageSearchBarComponent = (props) => {
  const { t } = useTranslation("image-search-bar");
  const [displayedSearchTerm, setDisplayedSearchTerm] = useState("");
  const debounceTimeoutRef = useRef(null);

  const handleSearchChange = (e) => {
    const value = e.target.value;
    setDisplayedSearchTerm(value);

    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }

    debounceTimeoutRef.current = setTimeout(() => {
      if (value.length >= 3 || value.length <= 0) {
        props.imageSearchActions.setSearchTerm(value);
      }
    }, 500); // debounce of 500ms
  };

  return (
    <div className="flex flex-wrap -mx-3 mt-4">
      <div className="w-full md:w-8/12 px-3 mb-3 md:mb-0">
        <input
          type="text"
          className="block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder={t("search_images")}
          value={displayedSearchTerm}
          onChange={handleSearchChange}
        />
      </div>
      <div className="w-full md:w-4/12 px-3">
        <TagSearchAndSelectInput
          imageFilterActions={{
            setFilterTagIds: props.imageSearchActions.setFilterTagIds,
          }}
        />
      </div>
    </div>
  );
};

export const ImageSearchBar = memo(ImageSearchBarComponent);
