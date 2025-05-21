import React, { useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { ToastContainer, toast } from 'react-toastify';
import BasicInfo from '../../components/customers/BasicInfo';
import OtherInfo from '../../components/customers/OtherInfo';
import 'react-toastify/dist/ReactToastify.css';
import { AiOutlineWarning } from 'react-icons/ai';
import { useRouter } from "next/router";
import { createUserWithEmailAndPassword } from "firebase/auth";
import { app, db, auth } from "../../firebase.config";
import { setDoc, doc } from 'firebase/firestore';
import logo from '../../public/images/logo.png';
import { getSession, signIn } from 'next-auth/react';
import { useForm } from 'react-hook-form';

const Register = () => {
  const [page, setPage] = useState(0);
  const [isAlert, setIsAlert] = useState(false);
  const [selectedCrypto, setSelectedCrypto] = useState([]);
  const router = useRouter();

  const notifyRequiredFields = () => toast.warn('Fill in required fields!', { position: "bottom-center", autoClose: 1000, theme: "dark" });
  const notifySuccess = () => toast.success('Account Created. Logging in...', { position: "top-center", autoClose: 5000, theme: "dark" });
  const notifyError = () => toast.error('Error creating profile. Try Again', { position: "top-center", autoClose: 1500, theme: "dark" });
  const userWriteError = () => toast.error('Error adding profile details!', { position: "bottom-center", autoClose: 1500, theme: "dark" });
  const loginError = () => toast.error('Automatic login failed. Redirecting...', { position: "top-center", autoClose: 2000, theme: "dark" });

  const [formData, setFormData] = useState({
    username: "",
    email: "",
    firstName: "",
    lastName: "",
    profilePhoto: "",
    coverPhoto: "",
    password: "",
    verified: false,
    bio: "",
    confirmPassword: "",
    currentPlan: "",
    address: "",
    country: "",
    city: "",
    state: "",
    phoneNumber: "",
    sex: "",
  });

  const PageDisplay = () => page === 0
    ? <BasicInfo formData={formData} isAlert={isAlert} setFormData={setFormData} />
    : <OtherInfo formData={formData} isAlert={isAlert} setFormData={setFormData} setSelectedCrypto={setSelectedCrypto} />;

  const { handleSubmit } = useForm();

  const submitHandler = async () => {
    const {
      username, email, firstName, lastName, password,
      confirmPassword, address
    } = formData;

    if (
      password === confirmPassword &&
      username && email && firstName && lastName && password && address
    ) {
      try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const uid = userCredential.user.uid;

        const userData = {
          id: uid,
          username,
          email,
          firstName,
          lastName,
          profilePhoto: formData.profilePhoto || '',
          coverPhoto: formData.coverPhoto || '',
          verified: false,
          bio: formData.bio || '',
          currentPlan: formData.currentPlan || '',
          address,
          country: formData.country || '',
          city: formData.city || '',
          state: formData.state || '',
          phoneNumber: formData.phoneNumber || '',
          sex: formData.sex || '',
          crypto: selectedCrypto,
          createdAt: new Date().toISOString(),
          role: "customer",
        };

        await setDoc(doc(db, "users", uid), userData);

        notifySuccess();

        const result = await signIn("credentials", {
          redirect: false,
          email,
          password,
        });

        if (result?.error) {
          loginError();
          router.push("/login");
        } else {
          router.push("/customer");
        }
      } catch (err) {
        console.error(err);
        notifyError();
      }
    } else {
      notifyRequiredFields();
    }
  };

  return (
    <div className='font-montserrat flex flex-col gap-8 items-center justify-center mb-20'>
      <div className="flex items-center w-full justify-between bg-lightBlack text-white">
        <div className="relative h-80 w-80 flex items-center">
          <Image src={logo} alt="crypto-logo" width={170} height={45} />
        </div>
        <div className="flex-col items-center px-2 md:flex md:flex-row md:gap-4">
          <h3 className="text-sm">Already have an account?</h3>
          <button className="text-s cursor-pointer rounded-md bg-primaryYellow p-1 text-black md:py-1 md:px-6">
            <Link href="/login"><p>Sign In</p></Link>
          </button>
        </div>
      </div>

      <div className="h-2.5 w-[25%] rounded-full bg-midGrey">
        {page === 0 ? (
          <>
            <div className="h-2.5 w-[50%] rounded-full bg-yellow-500"></div>
            <div className="flex justify-between gap-2 text-[9px] md:text-xs">
              <p className="font-semibold ">Basic Info</p>
              <p className="text-gray-400">Other Info</p>
            </div>
          </>
        ) : (
          <>
            <div className="h-2.5 w-[100%] rounded-full bg-yellow-500"></div>
            <div className="flex justify-between gap-2 text-[9px] md:text-xs">
              <p className="text-gray-400 ">Basic Info</p>
              <p className="font-semibold">Other Info</p>
            </div>
          </>
        )}
      </div>

      <div className="mt-6 flex w-screen flex-col gap-8 md:mt-20 md:items-center md:justify-center"> 
        {PageDisplay()}
      </div>

      {page === 0 ? (
        <button
          className="cursor-pointer rounded-lg bg-primaryYellow p-2 px-28"
          onClick={() => setPage((currPage) => currPage + 1)}
        >Continue</button>
      ) : (
        <form className="flex flex-col md:grid grid-cols-2 gap-x-8 md:w-[70%]  gap-y-6 md:gap-y-8 p-8 font-montserrat">
          <button className="p-2 px-20 rounded-lg bg-lightBlack/90 text-white cursor-pointer"
            onClick={() => setPage((currPage) => currPage - 1)}
          >Previous</button>
          <button
            type="submit"
            onClick={handleSubmit(submitHandler)}
            className="p-2 px-20 rounded-lg bg-primaryYellow cursor-pointer"
          >Submit</button>
        </form>
      )}

      <ToastContainer />
    </div>
  );
};

export default Register;

export async function getServerSideProps(context) {
  const session = await getSession(context);
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
