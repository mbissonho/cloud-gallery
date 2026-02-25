import {
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  createContext,
} from "react";
import { useParams, useNavigate, useLocation } from "react-router-dom";
import imageService from "../services/image-service";

const ImageListContext = createContext();

const useImageList = () => {
  const context = useContext(ImageListContext);
  if (context === undefined)
    throw new Error("useImageList should be used inside of ImageListProvider");

  return context;
};

const ImageListProvider = ({ children }) => {
  const { page: pageParam } = useParams();
  const navigate = useNavigate();
  const location = useLocation();

  const [initialPage] = useState(pageParam ? parseInt(pageParam) : 1);

    // State (Data that changes frequently)
  const [searchTerm, setSearchTerm] = useState("");
  const [pagination, setPagination] = useState({
    page: initialPage,
    perPage: 4,
    totalPages: 1,
  });
  const [filterTagIds, setFilterTagIds] = useState([]);
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

    // ACTIONS (Main Optimization)
    // We create a memoized object with empty dependencies [].
    // This ensures that the 'actions' object REMAINS THE SAME during the entire app lifecycle.
    // React guarantees that useState 'set' functions are stable, so it is safe.
  const actions = useMemo(
    () => ({
      setSearchTerm,
      setPagination,
      setFilterTagIds,
      // Add new possible functions that not depend from state here
    }),
    []
  );

    // Complex Functions (State dependent)
    // getItems needs to read the current state, so it changes when the state changes.
  const getItems = useCallback(async () => {
    setLoading(true);
    setError(null);

    await imageService
      .search({
        page: pagination.page,
        perPage: pagination.perPage,
        query: searchTerm,
        filterTagIds: filterTagIds,
      })
      .then(async (response) => {
        const body = response.data;

        setItems(body?.data ?? []);
        setPagination((prev) => ({
          ...prev,
          page: body?.meta?.last_page === 1 ? 1 : prev.page,
          totalPages: body?.meta?.last_page ?? 1,
        }));
      })
      .catch((err) => {
        setError("Failed while loading items");
        console.error(err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [searchTerm, filterTagIds, pagination.page, pagination.perPage]);

  // Effect to search for items when filters change.
  useEffect(() => {
    getItems();
  }, [getItems]); // getItems já inclui as dependências necessárias no useCallback

  // Effect to reset page when filters change
  useEffect(() => {
    // Only reset the page if it's not the initial mount and filters have actually changed
    // We compare with initialPage to avoid resetting during initial hydration
    if (searchTerm === "" && filterTagIds.length === 0) return;

    setPagination((prev) => {
      if (prev.page !== 1) {
        return { ...prev, page: 1 };
      }
      return prev;
    });
  }, [searchTerm, filterTagIds]);

  // Effect to sync URL with pagination
  useEffect(() => {
    const isMainList = location.pathname === "/" || location.pathname.startsWith("/page/");
    if (isMainList) {
      if (pagination.page === 1) {
        if (location.pathname !== "/") {
          navigate("/", { replace: true });
        }
      } else {
        const newPath = `/page/${pagination.page}`;
        if (location.pathname !== newPath) {
          navigate(newPath);
        }
      }
    }
  }, [pagination.page, navigate, location.pathname]);

  // Effect to sync pagination with URL (for back/forward browser buttons)
  useEffect(() => {
    const pageInUrl = pageParam ? parseInt(pageParam) : 1;
    if (pageInUrl !== pagination.page) {
      setPagination(prev => ({ ...prev, page: pageInUrl }));
    }
  }, [pageParam]);

  const exposedContextValue = useMemo(
    () => ({
      // We expose the 'actions' object separately for memoized components.
      actions,

      // We also spread the actions to the root directory for convenience (optional, but useful).
      ...actions,

      // States
      searchTerm,
      pagination,
      filterTagIds,
      items,
      loading,
      error,

      // Complex functions
      getItems,
    }),
    [
      actions,
      searchTerm,
      pagination,
      filterTagIds,
      items,
      loading,
      error,
      getItems,
    ]
  );

  return (
    <ImageListContext.Provider value={exposedContextValue}>
      {children}
    </ImageListContext.Provider>
  );
};

export { ImageListProvider, useImageList };
