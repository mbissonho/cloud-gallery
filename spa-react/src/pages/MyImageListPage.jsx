import { useState, useMemo, useCallback } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";
import { DeleteButton } from "../components/DeleteButton";
import { EditButton } from "../components/EditButton";
import { ImageActionSection } from "../components/ImageActionSection";
import { ImageGrid } from "../components/ImageGrid";
import { ImageSearchBar } from "../components/ImageSearchBar";
import { StatusBadge } from "../components/StatusBadge";
import { DeleteImageDialog } from "../components/DeleteImageDialog";
import { useMyImageList } from "../contexts/MyImageListContext";
import imageService from "../services/image-service";

export default function MyImageListPage() {
  const navigate = useNavigate();
  const { t } = useTranslation("my-image-list-page");

  const [isOpen, setIsOpen] = useState(false);
  const [imageDeleteLoading, setImageDeleteLoading] = useState(false);
  const [imageToDelete, setImageToDelete] = useState(null);

  const { pagination, setPagination, items, actions, loading, error } =
    useMyImageList();

  const imageListProps = useMemo(
    () => ({
      pagination,
      setPagination,
      items,
      loading,
      error,
    }),
    [pagination, setPagination, items, loading, error]
  );

  const handleEdit = useCallback(
    (image) => {
      navigate(`/edit/${image.id}`, { state: image });
    },
    [navigate]
  );

  const handleDelete = useCallback((image) => {
    setImageToDelete(image);
    setIsOpen(true);
  }, []);

  const handleConfirmDelete = useCallback(
    async (image) => {
      if (!image) return;

      setImageDeleteLoading(true);

      try {
        await imageService.deleteImage({ imageId: image.id });

        actions.setLoading(true);

        setIsOpen(false);
        setImageDeleteLoading(false);

        setTimeout(() => {
          actions.setShouldRefetch(true);
        }, 1000);
      } catch (err) {
        console.error(err);
        actions.setLoading(false);
        setImageDeleteLoading(false);
      }
    },
    [actions]
  );

  const gridActionButtons = useMemo(
    () => [
      <EditButton key="edit" handleEdit={handleEdit} />,
      <DeleteButton key="delete" handleDelete={handleDelete} />,
      <StatusBadge key="status" />,
    ],
    [handleEdit, handleDelete]
  );

  return (
    <div className="container mx-auto">
      <h1 className="my-4 text-3xl font-bold text-gray-800">{t("title")}</h1>

      <ImageSearchBar imageSearchActions={actions} />

      <ImageActionSection items={items} />

      <ImageGrid imageList={imageListProps}>{gridActionButtons}</ImageGrid>

      <DeleteImageDialog
        isOpen={isOpen}
        onClose={setIsOpen}
        image={imageToDelete}
        onConfirm={handleConfirmDelete}
        isLoading={imageDeleteLoading}
      />
    </div>
  );
}
