import { useTranslation } from "react-i18next";
import { ImageSearchBar } from "../components/ImageSearchBar";
import { ImageGrid } from "../components/ImageGrid";
import { useImageList } from "../contexts/ImageListContext";

export default function ImageListPage() {
  const { t } = useTranslation("image-list-page");
  const { pagination, setPagination, items, actions, loading, error } =
    useImageList();

  return (
    <div className="container mx-auto">
      <h1 className="my-4 text-3xl font-bold text-gray-800">{t("title")}</h1>
      <ImageSearchBar imageSearchActions={actions} />
      <ImageGrid
        imageList={{
          pagination,
          setPagination,
          items,
          loading,
          error,
        }}
      />
    </div>
  );
}
