import { yupResolver } from "@hookform/resolvers/yup";
import { useCallback, useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { useLocation, useNavigate, useParams } from "react-router-dom";
import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import * as yup from "yup";

import { TagSearchAndSelectInput } from "../components/TagSearchAndSelectInput";
import imageService from "../services/image-service";

export default function EditImagePage() {
  const location = useLocation();
  const { title, tag_names, tag_ids, status } = location.state || {};
  const [details, setDetails] = useState({});
  const [error, setError] = useState(null);
  const { t } = useTranslation("edit-image-page");
  const [isLoading, setIsLoading] = useState(false);
  const [initialTags, setInitialTags] = useState([]);
  const params = useParams();

  const navigate = useNavigate();

  const schema = yup.object().shape({
    file_tag_ids: yup
        .array()
        .of(yup.number())
        .nullable()
        .test("at-least-one-tag", t("validation.tagsRequired"), (value) => {
          return value && value.length > 0;
        }),
    file_description: yup
        .string()
        .max(255, t("validation.descriptionMax"))
        .nullable(),
    file_status: yup
        .string()
        .oneOf(["AVAILABLE", "DISABLED"], t("validation.statusInvalid"))
        .required(t("validation.statusRequired")),
  });

  const {
    register,
    handleSubmit,
    setValue,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      file_tag_ids: [],
      file_description: "",
      file_status: status,
    },
  });

  useEffect(() => {
    const fetchImageData = async () => {
      setIsLoading(true);

      imageService
          .getImageDetails({ imageId: params.imageId, editMode: true })
          .then(async (response) => {
            const body = response.data;
            setDetails(body?.data ?? []);

            const imageData = body?.data;
            // Fill form with image data
            reset({
              file_description: imageData.description || "",
              file_status: status,
            });

            // Configure the initial tags for TagSearchAndSelectInput component
            if (tag_names?.length && tag_ids.length) {
              const tagsAsObject = tag_ids.map((id, index) => {
                return {
                  id: id,
                  name: tag_names[index],
                };
              });

              setInitialTags(tagsAsObject);
              // Define the value for react-hook-form
              setValue(
                  "file_tag_ids",
                  tagsAsObject.map((tag) => tag.id),
                  { shouldValidate: true }
              );
            }
          })
          .catch((error) => {
            if (error.response?.status === 404) {
              toast.error(t("error.imageNotFound"));
              navigate(-1);
            } else {
              toast.error(error.response?.data?.message || t("error.loadFailed"));
            }
            console.error(error);
          })
          .finally(() => {
            setIsLoading(false);
          });
    };

    fetchImageData();
  }, [tag_ids, tag_names, status, params, reset, t, navigate, setValue]);

  const handleTagsChange = useCallback(
      (selectedTagsArray) => {
        setValue(
            "file_tag_ids",
            selectedTagsArray.map((tag) => tag.id),
            { shouldValidate: true }
        );
      },
      [setValue]
  );

  const onSubmit = async (data) => {
    setIsLoading(true);

    const payload = {
      description: data.file_description,
      tag_ids: data.file_tag_ids,
      status: data.file_status,
    };

    imageService
        .updateImage({
          imageId: params.imageId,
          payload: JSON.stringify(payload),
        })
        .then(() => {
          toast.success(t("success.imageUpdate"));
          navigate("/my-image-list");
        })
        .catch((error) => {
          console.error("Erro na atualização da imagem:", error);
          if (error.response) {
            toast.error(error.response.data.message || t("error.updateFailed"));
          } else {
            toast.error(t("error.updateFailed"));
          }
        })
        .finally(() => {
          setIsLoading(false);
        });
  };

  if (isLoading && !initialTags.length) {
    return (
        <div className="container mx-auto p-4 animate-pulse">
          {/* Título Skeleton */}
          <div className="h-9 bg-gray-300 rounded w-1/3 my-4"></div>

          <div className="bg-white p-6 rounded-lg shadow-md">
            {/* Tags Label & Input Skeleton */}
            <div className="mb-4">
              <div className="h-4 bg-gray-300 rounded w-24 mb-2"></div>
              <div className="h-10 bg-gray-200 rounded w-full"></div>
            </div>

            {/* Description Label & Textarea Skeleton */}
            <div className="mb-6">
              <div className="h-4 bg-gray-300 rounded w-32 mb-2"></div>
              <div className="h-28 bg-gray-200 rounded w-full"></div>
            </div>

            {/* Status Label & Select Skeleton */}
            <div className="mb-6">
              <div className="h-4 bg-gray-300 rounded w-20 mb-2"></div>
              <div className="h-10 bg-gray-200 rounded w-full"></div>
            </div>

            {/* Button Skeleton */}
            <div className="h-10 bg-gray-300 rounded w-32"></div>
          </div>
        </div>
    );
  }

  return (
      <div className="container mx-auto p-4">
        <h1 className="my-4 text-3xl font-bold text-gray-800">
          {t("title", { imageName: title })}
        </h1>

        <form
            onSubmit={handleSubmit(onSubmit)}
            className="bg-white p-6 rounded-lg shadow-md"
        >
          {/* Tags */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              {t("form.tagsLabel")}
            </label>
            <TagSearchAndSelectInput
                mode="select"
                onTagsChange={handleTagsChange}
                initialSelectedTags={initialTags}
            />
            {errors.file_tag_ids && (
                <p className="text-red-500 text-xs italic mt-1">
                  {errors.file_tag_ids.message}
                </p>
            )}
          </div>

          {/* Description */}
          <div className="mb-6">
            <label
                htmlFor="file_description"
                className="block text-gray-700 text-sm font-bold mb-2"
            >
              {t("form.descriptionLabel")}
            </label>
            <textarea
                id="file_description"
                {...register("file_description")}
                rows="4"
                className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                    errors.file_description ? "border-red-500" : ""
                }`}
                placeholder={t("form.descriptionPlaceholder")}
            ></textarea>
            {errors.file_description && (
                <p className="text-red-500 text-xs italic mt-1">
                  {errors.file_description.message}
                </p>
            )}
          </div>

          {/* Status */}
          <div className="mb-6">
            <label
                htmlFor="file_status"
                className="block text-gray-700 text-sm font-bold mb-2"
            >
              {t("form.statusLabel")}
            </label>
            <select
                id="file_status"
                {...register("file_status")}
                className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
                    errors.file_status ? "border-red-500" : ""
                }`}
            >
              <option value="AVAILABLE">{t("form.statusAvailable")}</option>
              <option value="DISABLED">{t("form.statusDisabled")}</option>
            </select>
            {errors.file_status && (
                <p className="text-red-500 text-xs italic mt-1">
                  {errors.file_status.message}
                </p>
            )}
          </div>

          {/* Submit button */}
          <div className="flex items-center justify-between">
            <button
                type="submit"
                className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                disabled={isLoading}
            >
              {isLoading ? t("form.savingButton") : t("form.saveButton")}
            </button>
          </div>
        </form>
      </div>
  );
}