import React from "react";
import { mdiMenu } from "@mdi/js";
import Icon from "@mdi/react";
import SpeedMeter from "./SpeedMeter";

const SettingsTray = (props) => {
  const {
    csrfToken,
    activeChat,
    activePanel,
    selectedMode,
    selectedFunction,
    selectedModel,
    setSelectedMode,
    setSelectedFunction,
    setSelectedModel,
  } = props;

  const getDisplayNameModeAndModel = () => {
    if (selectedMode !== "ChatGpt" || activeChat === null) {
      return "";
    }

    let modelName;
    switch (activeChat.model) {
      case "gpt-4":
        modelName = "GPT-4";
        break;
      case "gpt-3.5-turbo":
        modelName = "GPT-3.5-Turbo";
        break;
      default:
        modelName = "GPT-4-Turbo";
    }
    const modeName =
      Number(activeChat.function_id) === 1
        ? "ブラウジングモード"
        : "通常モード";

    return `${modelName} - ${modeName}`;
  };

  return (
    <>
      <div className="settings-tray d-flex">
        <div className="left d-flex flex-md-nowrap col-xl-3 col-lg-4 col-md-5 col-6">
          <button
            className="btn d-block d-md-none p-0"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvas"
            role="button"
          >
            <Icon path={mdiMenu} size={1} color="white" />
          </button>
          <select
            value={selectedMode}
            onChange={(e) => {
              setSelectedMode(e.target.value);
            }}
            className={`settings-tray-select select-mode ${
              selectedMode === "knowledge" ? "" : "gpt-mode"
            }`}
          >
            <option value="ChatGpt">ChatGpt</option>
            <option value="knowledge">社内ナレッジ</option>
          </select>

          {selectedMode === "ChatGpt" &&
            (activeChat === null ? (
              <>
                <select
                  value={selectedModel}
                  onChange={(e) => {
                    setSelectedModel(e.target.value);
                  }}
                  className="settings-tray-select"
                >
                  <option value="gpt-4-turbo">gpt-4-turbo</option>
                  <option value="gpt-4">gpt-4</option>
                  <option value="gpt-3.5-turbo">gpt-3.5</option>
                </select>
                <select
                  value={selectedFunction}
                  onChange={(event) => {
                    setSelectedFunction(event.target.value);
                  }}
                  className="settings-tray-select"
                >
                  <option value="none">通常モード</option>
                  <option value="function 1">ブラウジングモード</option>
                </select>
              </>
            ) : (
              ""
            ))}
        </div>
        {activePanel === "chat" && (
          <div className="col-xl-9 col-lg-8 col-md-7 col-6 right d-flex justify-content-end justify-content-md-between">
            <p
              style={{
                marginBottom: "0",
                visibility:
                  selectedMode === "ChatGpt" && activeChat !== null
                    ? "visible"
                    : "hidden",
              }}
            >
              {getDisplayNameModeAndModel()}
            </p>

            <div className="d-none d-md-flex align-items-center flex-end">
              <SpeedMeter csrfToken={csrfToken} />
            </div>
          </div>
        )}
      </div>
      <style jsx>{`
        .settings-tray {
          padding: 10px 15px;
        }
        .settings-tray-select {
          background: #f5962f;
          color: white;
          font-size: 16px;
          border: none;
          margin-left: 10px;
        }
        .settings-tray-select.select-mode {
          font-size: 18px;
          margin-left: 0;
        }
        @media screen and (max-width: 779px) {
          .settings-tray-select {
            font-size: 14px;
          }
          .settings-tray-select.select-mode.gpt-mode {
            max-width: 80px;
            font-size: 14px;
            margin-left: 5px;
          }
          .settings-tray {
            padding: 10px 5px;
          }
        }
      `}</style>
    </>
  );
};

export default SettingsTray;
