import NextAuth from "next-auth";
import GoogleProvider from "next-auth/providers/google";
import CredentialsProvider from "next-auth/providers/credentials";
import { signInWithEmailAndPassword } from "firebase/auth";
import { auth, db } from "@firebase";
import { collection, query, where, getDocs } from "firebase/firestore";

export default NextAuth({
  session: {
    strategy: "jwt",
  },

  providers: [
    CredentialsProvider({
      name: "Credentials",
      credentials: {
        email: { label: "Email", type: "text" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        const { email, password } = credentials;

        try {
          // Step 1: Sign in using Firebase Auth
          const userCredential = await signInWithEmailAndPassword(auth, email, password);
          const uid = userCredential.user.uid;
          const userEmail = userCredential.user.email;

          // Step 2: Get user data from Firestore
          const userRef = collection(db, "users");
          const q = query(userRef, where("id", "==", uid));
          const querySnapshot = await getDocs(q);

          if (querySnapshot.empty) {
            console.error("Firestore: No user document found for UID:", uid);
            throw new Error("No user profile found in Firestore.");
          }

          const userData = querySnapshot.docs[0].data();

          // Step 3: Return safe user object
          return {
            id: uid,
            email: userEmail,
            role: userData.role,
            name: `${userData.firstName} ${userData.lastName}`,
            profilePhoto: userData.profilePhoto || "",
            firstName: userData.firstName || "",
            lastName: userData.lastName || "",
          };
        } catch (error) {
          console.error("Authorize error:", error.message);
          return null; // Returning null triggers the "Couldn't log you in" error
        }
      },
    }),

    GoogleProvider({
      clientId: process.env.GOOGLE_ID || "",
      clientSecret: process.env.GOOGLE_SECRET || "",
    }),
  ],

  callbacks: {
    async session({ session, token }) {
      session.user.id = token.id;
      session.user.email = token.email;
      session.user.role = token.role;
      session.user.profilePhoto = token.profilePhoto;
      session.user.firstName = token.firstName;
      session.user.lastName = token.lastName;
      return session;
    },
    async jwt({ token, user }) {
      if (user) {
        token.id = user.id;
        token.email = user.email;
        token.role = user.role;
        token.profilePhoto = user.profilePhoto;
        token.firstName = user.firstName;
        token.lastName = user.lastName;
      }
      return token;
    },
  },

  pages: {
    signIn: "/login",
    signUp: "/register",
  },

  secret: process.env.NEXTAUTH_SECRET,
});
