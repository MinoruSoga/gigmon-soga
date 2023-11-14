import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useLocation } from "react-router";

const ProtectedRoute = (props) => {
  const navigate = useNavigate();
  const location = useLocation();
  const [userPlanId, setUserPlanId] = useState(null);

  useEffect(() => {
    const fetchPlanId = async () => {
      const csrfToken = props.token;
      try {
        const response = await fetch("/api/user/plan", {
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
          },
          credentials: "include",
        });

        const data = await response.json();
        setUserPlanId(data.plan_id);
      } catch (error) {
        console.error("Error fetching user's plan ID: ", error);
        setUserPlanId(null);
      }
    };
    fetchPlanId();
  }, []);

  if (userPlanId === null) {
    return <div>Loading...</div>;
  }

  let ComponentToRender = props.component;

  return userPlanId > 0 ? (
    <ComponentToRender {...props} />
  ) : (
    navigate("/no-access", { replace: true })
  );
};

export default ProtectedRoute;
