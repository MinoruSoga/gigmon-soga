/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other helpers. It's a great starting point while
 * building robust, powerful web applications using React + Laravel.
 */

require("./bootstrap");

/**
 * Next, we will create a fresh React component instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

require("./components/Chat");

import React from "react";
import { render } from "react-dom";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import ReactChat from "./routes/chat";
import NotFound from "./routes/404";
import Payments from "./routes/payments";
import ProtectedRoute from "./routes/protectedRoute";
import NoAccess from "./routes/noAccess";
import "./i18n";

const root = document.getElementById("react-root");
const propsContainer = document.getElementById("react-root");
const props = Object.assign({}, propsContainer.dataset);

const router = createBrowserRouter([
  {
    path: "/react-chat",
    element: <ProtectedRoute component={ReactChat} {...props} />,
  },
  {
    path: "/payments",
    element: <Payments {...props} />,
  },
  {
    path: "*",
    element: <NotFound {...props} />,
  },
  {
    path: "/no-access",
    element: <NoAccess />,
  },
]);

render(
  <React.StrictMode>
    <RouterProvider router={router} {...props} />
  </React.StrictMode>,
  root
);
