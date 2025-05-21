import React, { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { signIn, getSession } from "next-auth/react";
import { useRouter } from "next/router";
import { Input, useInput } from "@nextui-org/react";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { AiFillCloseCircle } from "react-icons/ai";
import Layout from '../components/ui/Layout';

const Login = ({ session }) => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState(null);
  const router = useRouter();

  const { value } = useInput("");

  const validateEmail = (value) => {
    return value.match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i);
  };

  const helper = React.useMemo(() => {
    if (!value) return { text: "", color: "" };
    const isValid = validateEmail(value);
    return {
      text: isValid ? "Correct email" : "Enter a valid email",
      color: isValid ? "success" : "error",
    };
  }, [value]);

  const notifyLoginError = () => {
    toast.error(errorMessage, {
      position: "bottom-right",
      autoClose: 1500,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      theme: "dark",
    });
  };

  const loginHandler = async (e) => {
    e.preventDefault();
    const payload = { email, password };

    if (!payload.email || !payload.password) {
      setErrorMessage("Please fill in all fields");
      notifyLoginError();
      return;
    }

    setErrorMessage(null);
    const result = await signIn("credentials", {
      ...payload,
      redirect: false,
    });

    if (!result.error) {
      const session = await getSession();
      router.push(`/${session.user.role}`);
    } else {
      alert("Couldn't log you in. Check details and try again!");
    }
  };

  return (
    <div className="bg-white w-screen h-screen bg-contain bg-no-repeat bg-center flex font-montserrat">
      <ToastContainer
        position="top-right"
        autoClose={5000}
        hideProgressBar={true}
        newestOnTop
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="light"
      />
      <div className="hidden lg:flex relative w-[70%] h-full">
        <Image
          src="/images/elon.jpg"
          layout="fill"
          className="object-contain"
          alt="Background"
        />
      </div>

      <div className="flex flex-col gap-2 w-[100%] -mt-32 md:-mt-0 items-center justify-center">
        {/* Logo removed */}
        <h1 className="flex flex-col text-center">
          <p className="text-3xl text-primaryYellow">Welcome back!</p>
          <p className="italic text-xl">Login to continue</p>
        </h1>
        <form onSubmit={loginHandler} className="flex flex-col w-[80%] md:w-[60%] gap-4">
          <Input
            clearable
            label="Email"
            placeholder="Enter your email"
            onChange={(e) => setEmail(e.target.value)}
          />
          <Input.Password
            clearable
            color="black"
            initialValue=""
            helperText="To reset password open a contact ticket."
            type="password"
            label="Password"
            placeholder="Enter your password"
            onChange={(e) => setPassword(e.target.value)}
          />
          <div className="flex mt-10 justify-between items-center">
            <button
              type="submit"
              className="px-4 py-2 bg-primaryYellow tracking-wider rounded-md hover:text-primaryYellow hover:bg-lightBlack"
            >
              Sign In
            </button>
            <button className="hover:text-primaryYellow/60 hover:underline">Forgotten Password?</button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default Login;

Login.getLayout = function getLayout(Login) {
  return <Layout>{Login}</Layout>;
};

export async function getServerSideProps() {
  const session = await getSession();
  if (session) {
    return {
      redirect: {
        destination: `/${session.user.role}`,
        permanent: false,
      },
    };
  }
  return {
    props: { session },
  };
}
