import { useState, createContext, useContext, useEffect } from "react";
import axiosClient from "../api/axios";

const AuthContext = createContext();

const INVALIDATED_PROFILE_PHOTO_KEY = "cloud-gallery-invalidated-profile-photo";

const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined)
    throw new Error("useAuth should be used inside of AuthProvider");
  return context;
};

const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [errors, setErrors] = useState(null);

  const [isLoadingAuth, setIsLoadingAuth] = useState(true);

  // isAuthenticated is derived from the user's existence.
  // This ensures that isAuthenticated never becomes 'true' if the user is 'null'.
  const isAuthenticated = !!user;

  const refreshUser = async () => {
    try {
      const response = await axiosClient.get("/user");
      setUser(response.data);
      return response.data;
    } catch (error) {
      setUser(null);
    }
  };

  useEffect(() => {
    const initAuth = async () => {
      await refreshUser();
      setIsLoadingAuth(false);
    };
    initAuth();
  }, []);

  const csrf = async () => axiosClient.get("/sanctum/csrf-cookie");

  const login = async ({ email, password }) => {
    setErrors(null);
    try {
      await csrf();
      await axiosClient.post("/login", { email, password });

      await refreshUser();

      return { signedIn: true };
    } catch (error) {
      const errorData = error.response?.data || {};
      setErrors(errorData);
      throw error;
    }
  };

  const logout = async () => {
    try {
      await axiosClient.post("/logout");
    } catch (error) {
      console.error("Erro ao fazer logout", error);
    } finally {
      setUser(null);
    }
  };

  const updateProfile = async (payload) => {
    const response = await axiosClient.put("/api/v1/profile/edit", payload);
    // Atualiza o objeto user local para refletir as mudanças (ex: novo nome) na UI
    await refreshUser();
    return response;
  };

  const getPreSignedUrlForProfilePhoto = async ({ name, mimeType }) => {
    return axiosClient.get("/api/v1/profile/s3_pre_signed_url", {
      params: {
        filename: name,
        content_type: mimeType,
      },
    });
  };

  const invalidateCurrentProfilePhoto = (currentProfilePhotoKey) => {
    if (currentProfilePhotoKey) {
      localStorage.setItem(
        INVALIDATED_PROFILE_PHOTO_KEY,
        currentProfilePhotoKey
      );
    }
  };

  const getInvalidatedProfilePhotoKey = () => {
    return localStorage.getItem(INVALIDATED_PROFILE_PHOTO_KEY);
  };

  const exposedContextValue = {
    user,
    isAuthenticated,
    isLoadingAuth,
    errors,
    login,
    logout,
    refreshUser,
    updateProfile,
    getPreSignedUrlForProfilePhoto,
    invalidateCurrentProfilePhoto,
    getInvalidatedProfilePhotoKey,
  };

  return (
    <AuthContext.Provider value={exposedContextValue}>
      {!isLoadingAuth && children}
    </AuthContext.Provider>
  );
};

export { AuthProvider, useAuth };
