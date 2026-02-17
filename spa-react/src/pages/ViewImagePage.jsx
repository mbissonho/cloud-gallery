import { useCallback, useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import placeholderImage from "../assets/placeholder.png";
import userProfilePlaceholder from "../assets/user-profile-placeholder.jpg";
import ImageLoader from "../components/ImageLoader";
import UserProfileImageLoader from "../components/UserProfileImageLoader";
import imageService from "../services/image-service";

export default function ViewImagePage() {
  const location = useLocation();
  const { id, title, tag_names: tags, thumbnail_url } = location.state || {};

  const [details, setDetails] = useState({});
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);

  const getDetails = useCallback(async () => {
    setLoading(true);

    await imageService
      .getImageDetails({ imageId: id })
      .then(async (response) => {
        const body = response.data;
        setDetails(body?.data ?? []);
      })
      .catch((err) => {
        setError("Failed while loading image details");
        console.error(err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [id]);

  useEffect(() => {
    getDetails();
  }, [id, getDetails]);

  return (
    <div className="flex justify-center p-4 sm:p-6 md:p-8 bg-gray-100 min-h-screen">
      <div className="w-full max-w-6xl bg-white rounded-lg shadow-xl p-6 sm:p-8 md:p-10">
        {loading ? (
          <div className="animate-pulse">
            <div className="flex flex-col md:flex-row gap-6 mb-8">
              <div className="flex-1 space-y-4">
                <div className="h-6 bg-gray-200 rounded w-3/4"></div>
                <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                <div className="space-y-2 pt-2">
                  <div className="h-4 bg-gray-200 rounded w-full"></div>
                  <div className="h-4 bg-gray-200 rounded w-5/6"></div>
                  <div className="h-4 bg-gray-200 rounded w-full"></div>
                </div>
              </div>

              <div className="w-full md:w-64 h-48 md:h-64 bg-gray-200 rounded-lg flex items-center justify-center relative overflow-hidden">
                <div className="w-24 h-24 bg-gray-300 rounded-full"></div>
              </div>

              {/* Right Column: Tags SKELETON */}
              <div className="w-full md:w-48 space-y-2">
                <div className="h-6 bg-gray-200 rounded w-24 mb-2"></div>
                <div className="h-8 bg-gray-200 rounded-full w-24"></div>
                <div className="h-8 bg-gray-200 rounded-full w-28"></div>
                <div className="h-8 bg-gray-200 rounded-full w-20"></div>
                <div className="h-8 bg-gray-200 rounded-full w-24"></div>
              </div>
            </div>

            {/* Bottom Section: Author Name and Additional Details SKELETON */}
            <div className="border-t border-gray-200 pt-6 mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div className="flex items-center space-x-4">
                <div className="w-12 h-12 bg-gray-200 rounded-full"></div>
                <div className="space-y-1">
                  <div className="h-5 bg-gray-200 rounded w-32"></div>
                  <div className="h-4 bg-gray-200 rounded w-48"></div>
                </div>
              </div>
              <div className="h-6 bg-gray-200 rounded w-48"></div>
            </div>
          </div>
        ) : (
          // Real content
          <div>
            <div className="flex flex-col md:flex-row gap-6 mb-8">
              {/* Left Column: Title, Author, Description */}
              <div className="flex-1 space-y-4">
                <h2 className="text-2xl font-semibold text-gray-700">
                  {title}
                </h2>
                <p className="text-md text-gray-600">
                  By: <span className="font-medium">{details.author_name}</span>
                </p>
                <p className="text-gray-700 leading-relaxed pt-2">
                  {details.description}
                </p>
              </div>

              {/* Middle Column: Thumbnail */}
              <div className="flex items-center">
                <ImageLoader
                  src={thumbnail_url}
                  className={"rounded-lg"}
                  placeholderSrc={placeholderImage}
                  image={{ title: title }}
                />
              </div>

              {/* Right Column: Tags */}
              <div className="flex item-center">
                <div className="w-full md:w-48 flex-shrink-0">
                  <h3 className="text-lg font-semibold text-gray-700 mb-2">
                    Tags
                  </h3>
                  <div className="flex flex-wrap gap-2">
                    {tags.map((tag, index) => (
                      <span
                        key={index}
                        className="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full"
                      >
                        {tag}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            </div>

            {/* Bottom Section: Author Name and Additional Details */}
            <div className="border-t border-gray-200 pt-6 mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div className="flex items-center space-x-4">
                <UserProfileImageLoader
                  src={details.author_photo}
                  className={"w-12 h-12 rounded-full object-cover shadow-sm"}
                  placeholderSrc={userProfilePlaceholder}
                  alt={details.author_name}
                />

                <div>
                  <p className="text-lg font-semibold text-gray-800">
                    {details.author_name}
                  </p>
                  {details.author_bio && (
                    <p className="text-sm text-gray-600">
                      {details.author_bio}
                    </p>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
