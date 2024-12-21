import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import avatarNoneAssetURL from '@/assets/avatar-none.png'
import { useToast } from '@/components/ui/toast/use-toast'


export const useAuthStore = defineStore('auth', () => {
  const storeError = useErrorStore()
  const socket = inject('socket')
  const { toast } = useToast()

  const user = ref(null)
  const token = ref('')

  const router = useRouter()

  const userName = computed(() => {
    return user.value.name
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
    return user.value.email
  })

  const userNickname = computed(() => {
    return user.value.nickname
  })

  const userType = computed(() => {
    return user.value.type
  })

  const userGender = computed(() => {
    return user.value.gender
  })

  const userPhotoUrl = computed(() => {
    const photoFile = user.value?.photo_filename
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
      user.value = responseUser.data.data
      socket.emit('login', user.value)
      repeatRefreshToken()
      router.push({ name: 'singleplayer' });
      return user.value
    } catch (e) {
      clearUser()
      if(e.response.status === 401) {
        toast({
          title: 'Failed to log in!',
          description:
            "The credentials you provided don't match an account.",
          variant: 'destructive',
        }) 
      }else{
        storeError.setErrorMessages(
          e.response.data.message,
          e.response.data.errors,
          e.response.status,
          'Authentication Error!'
        )
      }

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
        user.value = responseUser.data.data
        repeatRefreshToken()
        socket.emit('login', user.value)
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
      user.value = response.data.data
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

  const register = async (userData) => {
    storeError.resetMessages();
    if (user.value && user.value.type !== 'A') {
      storeError.setErrorMessages(
        'You are already registered and logged in!',
        [],
        [],
        'Registration Error!'
      );
      return false;
    }
    try {
      const formData = new FormData();
      for (const key in userData) {
        if(userData[key] !== null){
          formData.append(key, userData[key]);
        }
      }
      const response = await axios.post('/register', formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (response.data && user.value?.type != 'A') {
        socket.emit('notification_alert', response.data.data.id)
        return await login({ email: userData.email, password: userData.password });
      } else {
        router.back();
      }
    } catch (e) {
      console.log(e);
      storeError.setErrorMessages(
        e.response?.data?.message,
        e.response?.data?.errors || [],
        e.response?.status,
        'Registration Error!'
      );
      throw e;
    }
    return false;
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
    updatePhoto,
    register,
    clearUser,
  }
})
