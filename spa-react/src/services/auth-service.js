import axiosClient from "../api/axios";

class AuthService {
  async requestPasswordReset({ email }) {
    return axiosClient.post("/api/v1/auth/forgot-password", { email });
  }

  async resetPassword({ token, email, password, passwordConfirmation }) {
    return axiosClient.post("/api/v1/auth/reset-password", {
      token,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });
  }
}

export default new AuthService();
