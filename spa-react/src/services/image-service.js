import axiosClient from "../api/axios";

class ImageService {


  _buildParams({ page, perPage, query, filterTagIds }) {
    const params = new URLSearchParams();

    if (page) params.append("page", page);
    if (perPage) params.append("per_page", perPage);
    if (query) params.append("query", query);

    if (filterTagIds && filterTagIds.length > 0) {
      filterTagIds.forEach((tagId) => {
        params.append("tag_id", tagId);
      });
    }

    return params;
  }

  async search({ page, perPage, query = false, filterTagIds = [] }) {
    const params = this._buildParams({ page, perPage, query, filterTagIds });

    return axiosClient.get("/api/v1/image", { params });
  }

  async searchUserImages({ page, perPage, query = false, filterTagIds = [] }) {
    const params = this._buildParams({ page, perPage, query, filterTagIds });

    return axiosClient.get("/api/v1/image/of-user", { params });
  }

  async getImageDetails({ imageId, editMode = false }) {
    let url = `/api/v1/image/${imageId}/details`;

    if (editMode) {
      url += "/edit";
    }

    return axiosClient.get(url);
  }

  async deleteImage({ imageId }) {
    return axiosClient.delete(`/api/v1/image/${imageId}`);
  }

  async updateImage({ imageId, payload }) {
    return axiosClient.put(`/api/v1/image/${imageId}`, payload);
  }
}

export default new ImageService();
