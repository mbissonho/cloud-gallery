import { useTranslation } from "react-i18next";

export default function ProfilePage() {
  const { t: til } = useTranslation("profile-page");

  return (
    <div className="container mx-auto">
      <h1 className="my-4 text-3xl font-bold text-gray-800">{til("title")}</h1>
    </div>
  );
}
