import axiosClient from "../api/axios";

class CheckoutService {
  async createSession({ imageId, email }) {
    return axiosClient.post("/api/v1/checkout/session", {
      image_id: imageId,
      email,
    });
  }

  async getDownloadUrl({ token }) {
    return axiosClient.get(`/api/v1/checkout/download/${token}`);
  }
}

export default new CheckoutService();
