import React, { useState, useEffect, useRef, useCallback, memo } from "react";
import { useTranslation } from "react-i18next";
import tagService from "../services/tag-service";

const TagSearchAndSelectInputComponent = (props) => {
  const { t } = useTranslation("tag-search-and-select-input");
  const [tagSearchTerm, setTagSearchTerm] = useState("");
  const [suggestions, setSuggestions] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  // Use the initial tags passed via props, or an empty array.
  const [selectedTags, setSelectedTags] = useState(
    props.initialSelectedTags || []
  );

  const inputRef = useRef(null);
  const suggestionBoxRef = useRef(null);

  // Prop to determine the mode: 'filter' or 'select'
  const { mode = "filter", onTagsChange } = props;

  // Effect to call onTagsChange whenever selectedTags change.
  useEffect(() => {
    if (onTagsChange) {
      onTagsChange(selectedTags);
    }
  }, [selectedTags, onTagsChange]);

  useEffect(() => {
    const delayDebounceFn = setTimeout(async () => {
      if (tagSearchTerm.length >= 3) {
        setIsLoading(true);

        await tagService
          .search({ searchTerm: tagSearchTerm })
          .then(async (response) => {
            const body = response.data;

            // Filter and hide already selected tags.
            const newSuggestions = (body?.data || []).filter(
              (suggestedTag) =>
                !selectedTags.some((tag) => tag.id === suggestedTag.id)
            );
            setSuggestions(newSuggestions);
          })
          .catch((error) => console.error("Error fetching tags:", error))
          .finally(() => setIsLoading(false));
      } else {
        setSuggestions([]);
      }
    }, 300); // Debounce de 300ms

    return () => clearTimeout(delayDebounceFn);
  }, [tagSearchTerm]);

  // Handling with clicks outside the suggestion box
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        inputRef.current &&
        !inputRef.current.contains(event.target) &&
        suggestionBoxRef.current &&
        !suggestionBoxRef.current.contains(event.target)
      ) {
        setSuggestions([]);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const addTag = useCallback(
    (tag) => {
      if (!selectedTags.some((t) => t.id === tag.id)) {
        const newSelectedTags = [...selectedTags, tag];
        setSelectedTags(newSelectedTags);

        setSuggestions([]);
        setTagSearchTerm("");

        if (newSelectedTags.length >= 2 && mode === "filter") {
          inputRef.current.readOnly = true;
        }

        if (mode === "filter" && props.imageFilterActions) {
          props.imageFilterActions.setFilterTagIds(
            newSelectedTags.map((tag) => tag.id)
          );
        }
      }
    },
    [selectedTags, mode, props.imageFilterActions]
  );

  const removeTag = useCallback(
    (tagId) => {
      const newSelectedTags = selectedTags.filter((t) => t.id !== tagId);
      setSelectedTags(newSelectedTags);

      setSuggestions([]);
      setTagSearchTerm("");
      if (newSelectedTags.length < 2 && mode === "filter") {
        inputRef.current.readOnly = false;
      }

      if (mode === "filter" && props.imageFilterActions) {
        props.imageFilterActions.setFilterTagIds(
          newSelectedTags.map((tag) => tag.id)
        );
      }
    },
    [selectedTags, mode, props.imageFilterActions]
  );

  const handleKeyDown = useCallback(
    (e) => {
      if (
        e.key === "Backspace" &&
        tagSearchTerm === "" &&
        selectedTags.length > 0
      ) {
        removeTag(selectedTags[selectedTags.length - 1].id);
      }
    },
    [tagSearchTerm, selectedTags, removeTag]
  );

  return (
    <div className="mb-3 relative z-30">
      <div
        className="flex flex-wrap items-center gap-2 p-2 border border-gray-300 rounded-md focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 min-h-[38px]"
        onClick={() => inputRef.current.focus()}
      >
        {selectedTags.map((tag) => (
          <span
            key={tag.id}
            className="flex items-center bg-blue-500 text-white text-sm px-2 py-1 rounded-full"
          >
            {tag.name}
            <button
              type="button"
              className="ml-1 text-white hover:text-blue-100 focus:outline-none"
              aria-label="Remove tag"
              onClick={(e) => {
                e.stopPropagation();
                removeTag(tag.id);
              }}
            >
              <svg
                className="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M6 18L18 6M6 6l12 12"
                ></path>
              </svg>
            </button>
          </span>
        ))}
        <div className="flex-grow min-w-[50px]">
          <input
            ref={inputRef}
            type="text"
            className="w-full p-0 border-none focus:ring-0 focus:outline-none"
            id="tag-search-input"
            placeholder={
              selectedTags.length === 0
                ? t(
                    mode === "filter" ? "search_tags" : "select_tags_for_upload"
                  )
                : ""
            }
            value={tagSearchTerm}
            onChange={(e) => setTagSearchTerm(e.target.value)}
            onKeyDown={handleKeyDown}
            autoComplete={"off"}
            style={{ maxWidth: "200px" }}
          />
        </div>
      </div>

      {suggestions.length > 0 && (
        <ul
          ref={suggestionBoxRef}
          className="absolute w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto z-70"
          style={{
            maxWidth: inputRef.current
              ? inputRef.current.closest(".flex-wrap").offsetWidth
              : "auto",
          }}
        >
          {suggestions.map((tag) => (
            <li
              key={tag.id}
              className="px-3 py-2 cursor-pointer hover:bg-gray-100"
              onClick={() => addTag(tag)}
            >
              {tag.name}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export const TagSearchAndSelectInput = memo(TagSearchAndSelectInputComponent);
