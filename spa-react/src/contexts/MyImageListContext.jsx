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

const MyImageListContext = createContext();

const useMyImageList = () => {
  const context = useContext(MyImageListContext);
  if (context === undefined)
    throw new Error(
      "useMyImageList should be used inside of MyImageListProvider"
    );

  return context;
};

const MyImageListProvider = ({ children }) => {
  const { page: pageParam } = useParams();
  const navigate = useNavigate();
  const location = useLocation();

  const [initialPage] = useState(pageParam ? parseInt(pageParam) : 1);

    // State Definitions
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
  const [shouldRefetch, setShouldRefetch] = useState(false);

    // ACTIONS (Optimization)
    // We group all pure setters here.
    // Since useState setters are stable, the dependency array is empty [].
    // This 'actions' object will never change its reference.
  const actions = useMemo(
    () => ({
      setSearchTerm,
      setPagination,
      setFilterTagIds,
      setShouldRefetch,
      setLoading,
    }),
    []
  );

    // Functions with State Dependencies
    // getItems cannot be in 'actions' because it depends on the current state (searchTerm, etc.)
  const getItems = useCallback(async () => {
    setLoading(true);
    setError(null);

    await imageService
      .searchUserImages({
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
        setError("Failed while loading your items");
        console.error(err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [searchTerm, filterTagIds, pagination.page, pagination.perPage]);

  useEffect(() => {
    getItems();

    if (shouldRefetch) {
      setShouldRefetch(false);
    }
  }, [getItems, shouldRefetch]);

  // Effect to reset page when filters change
  useEffect(() => {
    // Only reset the page if it's not the initial mount and filters have actually changed
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
    const isMyList = location.pathname === "/my-image-list" || location.pathname.startsWith("/my-image-list/page/");
    if (isMyList) {
      if (pagination.page === 1) {
        if (location.pathname !== "/my-image-list") {
          navigate("/my-image-list", { replace: true });
        }
      } else {
        const newPath = `/my-image-list/page/${pagination.page}`;
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
      actions,
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
      getItems
    ]
  );

  return (
    <MyImageListContext.Provider value={exposedContextValue}>
      {children}
    </MyImageListContext.Provider>
  );
};

export { MyImageListProvider, useMyImageList };
