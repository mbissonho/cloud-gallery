import { yupResolver } from "@hookform/resolvers/yup";
import { useEffect, useRef, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { toast } from "react-toastify";
import * as yup from "yup";
import axios from "axios";
import profilePlaceholderImage from "../assets/user-profile-placeholder.jpg";
import removeNullAndBlankValues from "../functions/remove-null-and-blank-values";
import { useAuth } from "../contexts/AuthContext";

export default function EditProfilePage() {
  const {
    user,
    updateProfile,
    getPreSignedUrlForProfilePhoto,
    invalidateCurrentProfilePhoto,
    getInvalidatedProfilePhotoKey,
  } = useAuth();

  const { t } = useTranslation("profile-page");

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [profilePhotoKey, setProfilePhotoKey] = useState(null);

  const schema = yup.object().shape({
    name: yup.string().max(255, t("validation.descriptionMax")).nullable(),
    bio: yup.string().max(1500, t("validation.descriptionMax")).nullable(),
  });

  const {
    register,
    handleSubmit,
    setValue,
    watch,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      name: "",
      bio: "",
      profile_photo: null,
      password: "",
      new_password: "",
    },
  });

  const fileInputRef = useRef(null);
  const profilePhoto = watch("profile_photo");
  const bio = watch("bio");

  useEffect(() => {
    if (user) {
      const userData = user.data || user;

      const currentProfilePhotoKeyIsInvalidated =
        userData?.profile_photo_key === getInvalidatedProfilePhotoKey();

      reset({
        name: userData?.name || "",
        bio: userData?.bio || "",
        profile_photo: currentProfilePhotoKeyIsInvalidated
          ? profilePlaceholderImage
          : userData?.profile_photo_url,
        password: "",
        new_password: "",
      });

      setProfilePhotoKey(userData?.profile_photo_key);
    }
  }, [user, reset, getInvalidatedProfilePhotoKey]);

  const onSubmit = async (data) => {
    setIsSubmitting(true);

    const payload = removeNullAndBlankValues({
      name: data.name,
      bio: data.bio,
      new_password: data.new_password,
      password: data.password,
    });

    try {
      await updateProfile(payload);

      toast.success(t("success.profile_update"));

      setValue("password", "");
      setValue("new_password", "");
    } catch (error) {
      console.error("Erro na atualização:", error);
      const msg = error.response?.data?.message || t("error.updateFailed");
      toast.error(msg);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleImageChange = async (event) => {
    const file = event.target.files[0];

    if (!file) {
      toast.error(t("validation.file_required"));
      return;
    }

    try {
      const response = await getPreSignedUrlForProfilePhoto({
        name: file.name,
        mimeType: file.type,
      });

      const body = response.data;

      await axios.put(preSignedUrl, file, {
        headers: {
          "Content-Type": file.type,
        },
      });

      toast.success(t("success.profile_photo_upload_processing"));

      setValue("profile_photo", profilePlaceholderImage);

      invalidateCurrentProfilePhoto(profilePhotoKey);
    } catch (error) {
      console.error("Erro no upload da imagem:", error);
      toast.error(t("error.updateFailed"));
    }
  };

  if (!user) {
    return (
      <div className="container mx-auto px-4">
        <div className="my-4 h-8 w-48 bg-gray-200 rounded animate-pulse"></div>
        <div className="bg-white space-y-6 animate-pulse">
          <div className="flex flex-col items-center">
            <div className="h-32 w-32 bg-gray-200 rounded-full"></div>
            <div className="h-4 w-32 bg-gray-200 rounded mt-4"></div>
          </div>
          <div className="space-y-4">
            <div>
              <div className="h-4 w-20 bg-gray-200 rounded mb-2"></div>
              <div className="h-10 w-full bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto">
      <h1 className="my-4 text-3xl font-bold text-gray-800">
        {t("title_edit")}
      </h1>

      <form onSubmit={handleSubmit(onSubmit)} className="bg-white space-y-6">
        {/* --- Avatar Section --- */}
        <div className="flex flex-col items-center">
          <div className="relative group">
            <img
              src={profilePhoto || profilePlaceholderImage}
              alt={t("alt.avatar")}
              className={`w-32 h-32 rounded-full object-cover border-4 border-gray-100 shadow-sm`}
            />

            {/* Change Photo Button */}
            <button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              disabled={isSubmitting}
              className="absolute bottom-0 right-0 bg-gray-800 text-white p-2 rounded-full hover:bg-gray-700 focus:outline-none shadow-lg disabled:opacity-50"
              title={t("buttons.change_photo")}
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fillRule="evenodd"
                  d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0011.586 3H8.414a1 1 0 00-.707.293L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z"
                  clipRule="evenodd"
                />
              </svg>
            </button>
          </div>
          <input
            type="file"
            ref={fileInputRef}
            onChange={handleImageChange}
            accept="image/*"
            className="hidden"
          />
          <span className="text-xs text-gray-500 mt-2">{t("help.avatar")}</span>
        </div>

        {/* --- General Fields --- */}
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {t("labels.name")}
            </label>
            <input
              type="text"
              {...register("name")}
              maxLength={100}
              disabled={isSubmitting}
              className={`w-full px-3 py-2 border focus:outline-none rounded-md focus:ring-blue-500 focus:border-blue-500 ${
                errors.name ? "border-red-500" : "border-gray-300"
              }`}
            />
            {errors.name && (
              <p className="text-red-500 text-xs mt-1">{errors.name.message}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              {t("labels.bio")}
            </label>
            <textarea
              {...register("bio")}
              rows={4}
              maxLength={1500}
              disabled={isSubmitting}
              className="w-full px-3 py-2 border focus:outline-none border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            />
            <div className="flex justify-between mt-1">
              {errors.bio && (
                <p className="text-red-500 text-xs">{errors.bio.message}</p>
              )}
              <span className="text-xs text-gray-400">
                {t("help.bio_counter", { current: bio?.length || 0 })}
              </span>
            </div>
          </div>
        </div>

        {/* --- Security Area --- */}
        <div className="pt-4 mt-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            {t("labels.new_password")}
          </h3>

          <div className="grid gap-4 md:grid-cols-2">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                {t("labels.new_password")}
              </label>
              <input
                type="password"
                {...register("new_password")}
                autoComplete="new-password"
                disabled={isSubmitting}
                className={`w-full px-3 py-2 border focus:outline-none rounded-md focus:ring-blue-500 focus:border-blue-500 ${
                  errors.new_password ? "border-red-500" : "border-gray-300"
                }`}
              />
              {errors.new_password && (
                <p className="text-red-500 text-xs mt-1">
                  {errors.new_password.message}
                </p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                {t("labels.current_password")}
              </label>
              <input
                type="password"
                {...register("password")}
                autoComplete="current-password"
                disabled={isSubmitting}
                className={`w-full px-3 py-2 border focus:outline-none rounded-md focus:ring-blue-500 focus:border-blue-500 ${
                  errors.password ? "border-red-500" : "border-gray-300"
                }`}
              />
              {errors.password && (
                <p className="text-red-500 text-xs mt-1">
                  {errors.password.message}
                </p>
              )}
            </div>
          </div>
          <p className="text-xs text-gray-500 mt-2">
            {t("help.password_section")}
          </p>
        </div>

        {/* --- Buttons --- */}
        <div className="flex items-center justify-end gap-4 pt-4">
          <button
            type="submit"
            disabled={isSubmitting}
            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition flex items-center"
          >
            {isSubmitting && (
              <svg
                className="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  className="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  strokeWidth="4"
                ></circle>
                <path
                  className="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            )}
            {t("buttons.save")}
          </button>
        </div>
      </form>
    </div>
  );
}
