import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import avatarNoneAssetURL from '@/assets/avatar-none.png'

export const useAuthStore = defineStore('auth', () => {
  const storeError = useErrorStore()
  const socket = inject('socket')

  const user = ref(null)
  const token = ref('')

  const router = useRouter()

  const userName = computed(() => {
    return user.value.data.name
  })

  const getFirstLastName = (fullName) => {
    const names = fullName.trim().split(' ')
    const firstName = names[0] ?? ''
    const lastName = names.length > 1 ? names[names.length -1 ] : ''
    return (firstName + ' ' + lastName).trim()
  }

  const userFirstLastName = computed(() => {
    const names = userName.value.trim().split(' ')
    const firstName = names[0] ?? ''
    const lastName = names.length > 1 ? names[names.length - 1] : ''
    return (firstName + ' ' + lastName).trim()
  })

  const userEmail = computed(() => {
    return user.value.data.email
  })

  const userNickname = computed(() => {
    return user.value.data.nickname
  })

  const userType = computed(() => {
    return user.value.data.type
  })

  const userGender = computed(() => {
    return user.value.data.gender
  })

  const userPhotoUrl = computed(() => {
    const photoFile = user.value.data.photo_filename
    const basePath = "/storage/photos/"; // Base path for the images
    if (photoFile) {
      return axios.defaults.baseURL.replaceAll('/api', basePath + photoFile)
    }
    return avatarNoneAssetURL
  })

  // This function is "private" - not exported by the store
  const clearUser = () => {
    resetIntervalToRefreshToken()
    if (user.value) {
      socket.emit('logout', user.value)
    }
    user.value = null
    token.value = ''
    localStorage.removeItem('token')
    axios.defaults.headers.common.Authorization = ''
  }

  const login = async (credentials) => {
    storeError.resetMessages()
    try {
      const responseLogin = await axios.post('auth/login', credentials)
      token.value = responseLogin.data.token
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
      const responseUser = await axios.get('users/me')
      user.value = responseUser.data
      socket.emit('login', user.value)
      repeatRefreshToken()
      router.push({ name: 'singleplayer' });
      return user.value
    } catch (e) {
      clearUser()
      storeError.setErrorMessages(
        e.response.data.message,
        e.response.data.errors,
        e.response.status,
        'Authentication Error!'
      )
      return false
    }
  }

  const logout = async () => {
    storeError.resetMessages()
    try {
      await axios.post('auth/logout')
      clearUser()
      router.push({ name: 'home' });
      return true
    } catch (e) {
      clearUser()
      storeError.setErrorMessages(
        e.response.data.message,
        [],
        e.response.status,
        'Authentication Error!'
      )
      return false
    }
  }

  let intervalToRefreshToken = null

  const resetIntervalToRefreshToken = () => {
    if (intervalToRefreshToken) {
      clearInterval(intervalToRefreshToken)
    }
    intervalToRefreshToken = null
  }

  const repeatRefreshToken = () => {
    if (intervalToRefreshToken) {
      clearInterval(intervalToRefreshToken)
    }
    intervalToRefreshToken = setInterval(
      async () => {
        try {
          const response = await axios.post('auth/refreshtoken')
          token.value = response.data.token
          localStorage.setItem('token', token.value)
          axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
          return true
        } catch (e) {
          clearUser()
          storeError.setErrorMessages(
            e.response.data.message,
            e.response.data.errors,
            e.response.status,
            'Authentication Error!'
          )
          return false
        }
      },
      1000 * 60 * 110
    )
    return intervalToRefreshToken
  }

  const restoreToken = async function () {
    let storedToken = localStorage.getItem('token')
    if (storedToken) {
      try {
        token.value = storedToken
        axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
        const responseUser = await axios.get('users/me')
        user.value = responseUser.data
        repeatRefreshToken()
        return true
      } catch {
        clearUser()
        return false
      }
    }
    return false
  }


  // Fetch user data
  const fetchUser = async () => {
    storeError.resetMessages()
    try {
      const response = await axios.get('users/me') // API endpoint for fetching the authenticated user
      user.value = response.data
      return user.value
    } catch (e) {
      storeError.setErrorMessages(
        e.response?.data?.message,
        e.response?.data?.errors || [],
        e.response?.status,
        'Failed to fetch user data'
      )
      return null
    }
  }

  // Update user data
  const updateUser = async (updatedData) => {
    storeError.resetMessages();
    try {
      // Send the updated profile data to the backend
      const response = await axios.put("/profile", updatedData);
      user.value = response.data; // Update the store with the new user data
      return user.value;

    } catch (e) {
      storeError.setErrorMessages(
        e.response?.data?.message,
        e.response?.data?.errors || [],
        e.response?.status,
        'Failed to update user data'
      );
      throw e;
    }
  };

  const updatePhoto = async (file) => {
    const formData = new FormData();
    formData.append("photo", file);

    try {
      const response = await axios.post("/profile", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      return response.data.photo.replace("photos/", ""); // Returns the filename
    } catch (error) {
      console.error("Error uploading photo:", error);
      throw error;
    }
  };


  return {
    user,
    userName,
    userFirstLastName,
    userEmail,
    userNickname,
    userType,
    userGender,
    userPhotoUrl,
    getFirstLastName,
    login,
    logout,
    restoreToken,
    fetchUser,
    updateUser,
    updatePhoto
  }
})
