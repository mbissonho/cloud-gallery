import { useTranslation } from "react-i18next";
import { useState, useRef, useCallback } from "react";
import { useForm } from "react-hook-form";
import { yupResolver } from "@hookform/resolvers/yup";
import * as yup from "yup";
import axios from "axios"; // Axios padrão para o upload no S3 (sem cookies da API)
import axiosClient from "../api/axios"; // Axios configurado para comunicar com seu Backend
import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

import { TagSearchAndSelectInput } from "../components/TagSearchAndSelectInput";
import { useNavigate } from "react-router-dom";

export default function CreateImagePage() {
  const { t } = useTranslation("create-image-page");
  const [selectedFile, setSelectedFile] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const fileInputRef = useRef(null);
  const navigate = useNavigate();

  const schema = yup.object().shape({
    file_title: yup.string().required(t("validation.titleRequired")),
    file_tag_ids: yup
      .array()
      .of(yup.number())
      .nullable()
      .test(
        "at-least-one-tag",
        t("validation.tagsRequired"),
        (value) => value && value.length > 0
      ),
    file_description: yup
      .string()
      .max(255, t("validation.descriptionMax"))
      .nullable(),
  });

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      file_tag_ids: [],
    },
  });

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

  const handleFileChange = (event) => {
    setSelectedFile(event.target.files[0]);
  };

  const onSubmit = async (data) => {
    if (!selectedFile) {
      toast.error(t("validation.fileRequired"));
      return;
    }

    setIsLoading(true);

    try {
      const queryParams = new URLSearchParams();
      queryParams.append("filename", selectedFile.name);
      queryParams.append("content_type", selectedFile.type);
      queryParams.append("file_title", data.file_title);

      if (data.file_description) {
        queryParams.append("file_description", data.file_description);
      }

      if (data.file_tag_ids && data.file_tag_ids.length > 0) {
        data.file_tag_ids.forEach((tagId) => {
          queryParams.append("file_tag_ids[]", tagId);
        });
      }


      const response = await axiosClient.get(
        "/api/v1/image/s3_pre_signed_url",
        {
          params: queryParams,
        }
      );

      let { url: presignedUrl } = response.data;

      // TODO: Remove it later (Localstack related workaround)
      presignedUrl = presignedUrl.replace("localstack", "localhost");

      await axios.put(presignedUrl, selectedFile, {
        headers: {
          "Content-Type": selectedFile.type,
        },
      });

      toast.success(t("success.imageUpload"));

      setSelectedFile(null);
      setValue("file_title", "");
      setValue("file_tag_ids", []);
      setValue("file_description", "");
      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }

      navigate("/my-image-list");
    } catch (error) {
      console.error("Erro no upload da imagem:", error);
      if (error.response) {
        toast.error(error.response.data.message || t("error.uploadFailed"));
      } else {
        toast.error(t("error.uploadFailed"));
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="container mx-auto p-4">
      <h1 className="my-4 text-3xl font-bold text-gray-800">{t("title")}</h1>

      <form
        onSubmit={handleSubmit(onSubmit)}
        className="bg-white p-6 rounded-lg shadow-md"
      >
        <div className="mb-4">
          <label
            htmlFor="file_title"
            className="block text-gray-700 text-sm font-bold mb-2"
          >
            {t("form.titleLabel")}
          </label>
          <input
            type="text"
            id="file_title"
            {...register("file_title")}
            className={`shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ${
              errors.file_title ? "border-red-500" : ""
            }`}
            placeholder={t("form.titlePlaceholder")}
          />
          {errors.file_title && (
            <p className="text-red-500 text-xs italic mt-1">
              {errors.file_title.message}
            </p>
          )}
        </div>

        <div className="mb-4">
          <label
            htmlFor="image_file"
            className="block text-gray-700 text-sm font-bold mb-2"
          >
            {t("form.imageFileLabel")}
          </label>
          <input
            type="file"
            id="image_file"
            ref={fileInputRef}
            onChange={handleFileChange}
            accept="image/*"
            className="block w-full text-sm text-gray-500
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-full file:border-0
                       file:text-sm file:font-semibold
                       file:bg-blue-50 file:text-blue-700
                       hover:file:bg-blue-100"
          />
          {!selectedFile && (
            <p className="text-red-500 text-xs italic mt-1">
              {t("validation.fileRequired")}
            </p>
          )}
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-bold mb-2">
            {t("form.tagsLabel")}
          </label>
          <TagSearchAndSelectInput
            mode="select"
            onTagsChange={handleTagsChange}
          />
          {errors.file_tag_ids && (
            <p className="text-red-500 text-xs italic mt-1">
              {errors.file_tag_ids.message}
            </p>
          )}
        </div>

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

        <div className="flex items-center justify-between">
          <button
            type="submit"
            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
            disabled={isLoading}
          >
            {isLoading ? t("form.uploadingButton") : t("form.submitButton")}
          </button>
        </div>
      </form>
    </div>
  );
}
