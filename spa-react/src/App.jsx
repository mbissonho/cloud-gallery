import {lazy, Suspense} from "react";
import {BrowserRouter as Router, Route, Routes} from "react-router-dom";
import Header from "./components/Header";
import {ImageListProvider} from "./contexts/ImageListContext";
import {MyImageListProvider} from "./contexts/MyImageListContext";
import {ToastContainer} from "react-toastify";
import AuthGuard from "./guards/auth-guard";
import GuestGuard from "./guards/guest-guard";
import LoadingSpinner from "./components/LoadingSpinner";

const ImageListPage = lazy(() => import("./pages/ImageListPage"));
const ViewImagePage = lazy(() => import("./pages/ViewImagePage"));
const ViewProfilePage = lazy(() => import("./pages/ViewProfilePage"));
const EditProfilePage = lazy(() => import("./pages/EditProfilePage"));
const MyImageListPage = lazy(() => import("./pages/MyImageListPage"));
const CreateImagePage = lazy(() => import("./pages/CreateImagePage"));
const EditImagePage = lazy(() => import("./pages/EditImagePage"));
const LoginPage = lazy(() => import("./pages/LoginPage"));
const RegisterPage = lazy(() => import("./pages/RegisterPage"));


function App() {
  return (
    <Router>
      <ToastContainer />
      <Header />
      <Suspense fallback={<LoadingSpinner />}>
        <Routes>
          <Route
            path="/"
            element={
              <ImageListProvider>
                <ImageListPage />
              </ImageListProvider>
            }
          />
          <Route path="/view/:imageId" element={<ViewImagePage />} />
          <Route path="/profile/:userId" element={<ViewProfilePage />} />
          <Route element={<GuestGuard redirectTo="/" />}>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
          </Route>
          <Route element={<AuthGuard redirectTo="/my-image-list" />}>
            <Route path="/profile/edit" element={<EditProfilePage />} />
            <Route
              path="/my-image-list"
              element={
                <MyImageListProvider>
                  <MyImageListPage />
                </MyImageListProvider>
              }
            />
            <Route path="/edit/:imageId" element={<EditImagePage />} />
            <Route path="/new" element={<CreateImagePage />} />
          </Route>
        </Routes>
      </Suspense>
    </Router>
  );
}

export default App;
