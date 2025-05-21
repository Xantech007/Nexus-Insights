// firebaseConfig.js or firebase.js (depending on your project structure)

import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";
import { getFirestore } from "firebase/firestore";
import { getStorage } from "firebase/storage";

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyBiE_ulylBuXSrnNnoSEDHejvL5g8NTOSw",
  authDomain: "nexus-insights-346a7.firebaseapp.com",
  projectId: "nexus-insights-346a7",
  storageBucket: "nexus-insights-346a7.appspot.com", // corrected
  messagingSenderId: "640016887422",
  appId: "1:640016887422:web:cc1aa75c54871dbaaff22f"
};

// Initialize Firebase
export const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);
export const storage = getStorage(app);
