import React, { useEffect, useState } from "react";
import {
  mdiSpeedometerSlow,
  mdiSpeedometerMedium,
  mdiSpeedometer,
} from "@mdi/js";
import Icon from "@mdi/react";

const SpeedMeter = (props) => {
  const { csrfToken } = props;

  const [speed, setSpeed] = useState(null);

  const fetchSpeed = async () => {
    try {
      const response = await fetch("/api/speed", {
        method: "GET",
        headers: {
          "X-CSRF-TOKEN": csrfToken,
        },
      });
      const data = await response.json();
      setSpeed(data.status);
    } catch (error) {
      console.log("Error fetching speed: ", error);
    }
  };

  useEffect(() => {
    fetchSpeed();
    const intervalId = setInterval(fetchSpeed, 60 * 1000);

    return () => clearInterval(intervalId);
  }, []);

  return (
    <>
      <p className="mr-2 mb-0">ChatGPT速度</p>
      {speed === 1 && (
        <Icon
          path={mdiSpeedometerSlow}
          size={1}
          style={{ marginLeft: "0.5rem" }}
          color={"red"}
        />
      )}
      {speed === 2 && (
        <Icon
          path={mdiSpeedometerMedium}
          size={1}
          style={{ marginLeft: "0.5rem" }}
        />
      )}
      {speed === 3 && (
        <Icon
          path={mdiSpeedometer}
          size={1}
          style={{ marginLeft: "0.5rem" }}
          color={"green"}
        />
      )}
    </>
  );
};

export default SpeedMeter;
