import { BeatLoader } from "react-spinners";

const FullScreenLoader = ({ isLoading }) => {
  const loadingAnimationStyles = {
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    pointerEvents: "none",
    height: "100vh",
    zIndex: "1000",
    width: "100%",
    position: "absolute",
    top: "0",
  };

  const overlayStyles = {
    position: "absolute",
    top: 0,
    left: 0,
    width: "100%",
    height: "100%",
    backgroundColor: "rgba(0, 0, 0, 0.2)",
  };

  const beatLoaderStyles = {};

  return (
    isLoading && (
      <div style={loadingAnimationStyles}>
        <div style={overlayStyles}></div>
        <BeatLoader
          cssOverride={beatLoaderStyles}
          size={15}
          color={"#000000"}
        />
      </div>
    )
  );
};

export default FullScreenLoader;
