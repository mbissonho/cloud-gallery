import axiosClient from "../api/axios";

class TagService {
  async search({ searchTerm }) {
    return axiosClient.get("/api/v1/tag", {
      params: {
        query: searchTerm,
      },
    });
  }
}

export default new TagService();
