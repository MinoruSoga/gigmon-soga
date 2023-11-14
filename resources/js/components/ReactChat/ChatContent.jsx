import React from "react";
import { mdiAccountCircleOutline, mdiRobotOutline } from "@mdi/js";
import Icon from "@mdi/react";
import SendMsgForm from "./SendMsgForm";
import { BeatLoader } from "react-spinners";
import ReactMarkdown from "react-markdown";

const loadingAnimationStyles = {
  position: "fixed",
  top: 0,
  left: 0,
  width: "100%",
  height: "100%",
  display: "flex",
  justifyContent: "center",
  alignItems: "center",
  pointerEvents: "none",
};

const overlayStyles = {
  position: "absolute",
  top: 0,
  left: 0,
  width: "100%",
  height: "100%",
  backgroundColor: "rgba(0, 0, 0, 0.5)",
};

const beatLoaderStyles = {
  paddingBottom: "100px",
};

const ChatContent = (props) => {
  const {
    inputMessage,
    setInputMessage,
    submitMessage,
    chatPanelRef,
    conversation,
    isLoading,
  } = props;

  const scrollToBottom = () => {
    if (chatPanelRef.current) {
      chatPanelRef.current.scrollTop = chatPanelRef.current.scrollHeight;
    }
  };

  return (
    <div className="p-0 main-chat d-flex flex-column h-100">
      <div
        id="chat-panel"
        ref={chatPanelRef}
        style={{
          position: "relative",
          padding: "12px",
          position: "relative",
        }}
        className="chat-panel"
      >
        {conversation.length > 0 ? (
          conversation.map((message, i) => {
            if (!message) {
              return null;
            }

            return (
              <div key={`chat-mess-${i}`} className="row no-gutters">
                <div className={`col-md-12 d-flex chat-bubble`}>
                  <div className="chat-icon">
                    <Icon
                      path={
                        message.role === "user"
                          ? mdiAccountCircleOutline
                          : mdiRobotOutline
                      }
                      size={1.2}
                    />
                  </div>
                  <div className="">
                    <div className="title d-flex">
                      <div className="user-name">
                        {message.role === "user" ? "You" : "GiGMON"}
                      </div>
                    </div>
                    <div className="markdown-container">
                      <ReactMarkdown
                        children={message.content}
                        components={{
                          a: ({ node, ...props }) => (
                            <a
                              {...props}
                              target="_blank"
                              rel="noopener noreferrer"
                            />
                          ),
                          p: ({ node, ...props }) => (
                            <p
                              {...props}
                              style={{
                                whiteSpace: "pre-line",
                              }}
                            />
                          ),
                        }}
                      />
                    </div>
                  </div>
                </div>
              </div>
            );
          })
        ) : (
          <div
            style={{
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
              height: "100%",
              color: "black",
              fontSize: "20px",
            }}
          >
            質問を入力してください
          </div>
        )}
      </div>
      {/* Loading Animation */}
      {isLoading && (
        <div
          style={{
            ...loadingAnimationStyles,
            position: "absolute",
            top: 0,
            bottom: 0,
            left: 0,
            right: 0,
          }}
        >
          <div style={overlayStyles}></div>
          <BeatLoader
            cssOverride={beatLoaderStyles}
            size={15}
            color={"#000000"}
          />
        </div>
      )}
      <div className="row no-gutter mt-auto" style={{ zIndex: 2 }}>
        <div className="col-12 no-padding">
          <SendMsgForm
            isLoading={isLoading}
            submitMessage={submitMessage}
            inputMessage={inputMessage}
            setInputMessage={setInputMessage}
          />
        </div>
      </div>
    </div>
  );
};

export default ChatContent;
