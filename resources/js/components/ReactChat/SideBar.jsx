import React, { useState } from "react";
import {
  mdiStarOutline,
  mdiChatOutline,
  mdiAccountBoxOutline,
  mdiTrashCanOutline,
  mdiPlusCircleOutline,
  mdiChevronDown,
  mdiChevronUp,
} from "@mdi/js";
import Icon from "@mdi/react";
import Collapse from "react-bootstrap/Collapse";

const DISPLAY_COLUMNS = 20; // 表示文字数の状態変数
const DISPLAY_CHAT = 20;

const sideBarContent = (props) => {
  const [isChatAccordionOpen, setChatAccordionOpen] = useState(true);
  const [isPromptAccordionOpen, setPromptAccordionOpen] = useState(false);
  const [isCustomAccordionOpen, setCustomAccordionOpen] = useState(false);

  const [hoveredIndex, setHoveredIndex] = useState(null);
  const {
    activePanel,
    setActivePanel,
    activeChat,
    setActiveChat,
    isLoading,
    chatHistory,
    selectedMode,
    setInputMessage,
    csrfToken,
    setChatHistory,
    device,
  } = props;

  const handleDeleteChat = async (token) => {
    try {
      const mode = selectedMode === "ChatGpt" ? 1 : 2;
      const response = await fetch("/api/hideHistory", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          mode,
          conversationToken: token,
        }),
      });

      const data = await response.json();
      if (data.status === "success") {
        const updatedChats = chatHistory.filter(
          (chat) => chat.conversation_token !== token
        );
        if (activeChat?.conversation_token === token) {
          setActiveChat(null);
        }
        setChatHistory(updatedChats);
        setActivePanel("chat");
      } else {
        console.error(data.message);
      }
    } catch (error) {
      console.error("Failed to delete the chat", error);
    }
  };

  return (
    <div
      className="accordion accordion-flush my-accordion"
      id="accordionFlushExample"
    >
      <div className="accordion-item">
        <h2
          className={`accordion-header ${
            activePanel === "chat" ? "bg-orange" : "bg-inactive"
          }`}
          id="flush-headingOne"
        >
          <div
            className="flex-container"
            onClick={() => {
              setChatAccordionOpen(!isChatAccordionOpen);
            }}
          >
            <div className="flex-item">
              <Icon path={mdiChatOutline} size={1.3} />
              <h5 className="my-1 mx-2">チャット</h5>
            </div>
            {isChatAccordionOpen ? (
              <Icon path={mdiChevronUp} size={1} />
            ) : (
              <Icon path={mdiChevronDown} size={1} />
            )}
          </div>
        </h2>

        <Collapse in={isChatAccordionOpen}>
          <div>
            <div className="flex-center padding-5">
              <button
                className="styled-button"
                {...(device === "mobile"
                  ? { "data-bs-dismiss": "offcanvas" }
                  : {})}
                onClick={() => {
                  setActiveChat(null);
                  setActivePanel("chat");
                }}
              >
                <span>新しいチャット</span>
                <Icon path={mdiPlusCircleOutline} size={1} />
              </button>
            </div>

            <div id="flush-collapseOne" className="accordion-collapse">
              {isLoading && chatHistory.length <= 0 ? (
                <div className="loading">ローディング中</div>
              ) : (
                <React.Fragment>
                  {chatHistory.length > 0 ? (
                    <div>
                      {chatHistory.slice(0, DISPLAY_CHAT).map((chat, index) => (
                        <div key={index}>
                          <div
                            className={`friend-drawer friend-drawer--onhover ${
                              activeChat?.conversation_token ===
                              chat?.conversation_token
                                ? "active-chat"
                                : ""
                            } flex-container`}
                            {...(device === "mobile"
                              ? { "data-bs-dismiss": "offcanvas" }
                              : {})}
                            onClick={() => {
                              setInputMessage("");
                              setActiveChat(chat);
                            }}
                          >
                            <h6 className="my-1 margin-left-30">
                              {chat?.conversations?.[0]?.content?.length >
                              DISPLAY_COLUMNS
                                ? chat?.conversations?.[0]?.content?.slice(
                                    0,
                                    DISPLAY_COLUMNS
                                  ) + "..."
                                : chat?.conversations?.[0]?.content}
                            </h6>

                            <Icon
                              path={mdiTrashCanOutline}
                              size={1}
                              onClick={(event) => {
                                event.stopPropagation();
                                handleDeleteChat(chat?.conversation_token);
                              }}
                              color={hoveredIndex === index ? "red" : "#f5962f"}
                              onMouseEnter={() => setHoveredIndex(index)}
                              onMouseLeave={() => setHoveredIndex(null)}
                            />
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="flex-center height-100">
                      <p className="grey-text margin-top-05">
                        チャット履歴はありません
                      </p>
                    </div>
                  )}
                </React.Fragment>
              )}
            </div>
          </div>
        </Collapse>
      </div>

      {selectedMode === "ChatGpt" && (
        <div className="accordion-item">
          <h2
            className={`accordion-header ${
              activePanel === "generalPrompt" ? "bg-orange" : "bg-inactive"
            }`}
            id="flush-headingTwo"
          >
            <div
              className="flex-container"
              onClick={() => {
                setPromptAccordionOpen(!isPromptAccordionOpen);
              }}
            >
              <div className="flex-item">
                <Icon path={mdiStarOutline} size={1.3} />
                <h5 className="my-1 mx-2">おすすめのプロンプト</h5>
              </div>
              {isPromptAccordionOpen ? (
                <Icon path={mdiChevronUp} size={1} />
              ) : (
                <Icon path={mdiChevronDown} size={1} />
              )}
            </div>
          </h2>

          <Collapse in={isPromptAccordionOpen}>
            <div>
              <div className="flex-center padding-5">
                <button
                  className="styled-button"
                  {...(device === "mobile"
                    ? { "data-bs-dismiss": "offcanvas" }
                    : {})}
                  onClick={() => {
                    setActiveChat(null);
                    setActivePanel("generalPrompt");
                  }}
                >
                  <span>プロンプトを検索</span>
                  <Icon path={mdiPlusCircleOutline} size={1} />
                </button>
              </div>
            </div>
          </Collapse>
        </div>
      )}
      <div className="accordion-item">
        <h2
          className={`accordion-header ${
            activePanel === "individualPrompt" ? "bg-orange" : "bg-inactive"
          }`}
          id="flush-headingThree"
        >
          <div
            className="flex-container"
            onClick={() => {
              setCustomAccordionOpen(!isCustomAccordionOpen);
            }}
          >
            <div className="flex-item">
              <Icon path={mdiAccountBoxOutline} size={1.3} />
              <h5 className="my-1 mx-2">社内共有プロンプト</h5>
            </div>
            {isCustomAccordionOpen ? (
              <Icon path={mdiChevronUp} size={1} />
            ) : (
              <Icon path={mdiChevronDown} size={1} />
            )}
          </div>
        </h2>

        <Collapse in={isCustomAccordionOpen}>
          <div>
            <div className="flex-center padding-5">
              <button
                className="styled-button"
                {...(device === "mobile"
                  ? { "data-bs-dismiss": "offcanvas" }
                  : {})}
                onClick={() => {
                  setActiveChat(null);
                  setActivePanel("individualPrompt");
                }}
              >
                <span>社内プロンプトを検索</span>
                <Icon path={mdiPlusCircleOutline} size={1} />
              </button>
            </div>
          </div>
        </Collapse>
      </div>
      <style jsx>{`
        .my-accordion {
          min-width: 320px;
        }

        .flex-container {
          display: flex;
          justify-content: space-between;
          align-items: center;
          width: 100%;
        }

        .flex-item {
          display: flex;
          align-items: center;
        }

        .flex-center {
          display: flex;
          justify-content: center;
        }

        .padding-5 {
          padding: 5px;
        }

        .styled-button {
          display: flex;
          align-items: center;
          justify-content: space-between;
          background-color: transparent;
          color: #f5962f;
          border: 2px solid #f5962f;
          border-radius: 5px;
          padding: 3px 6px;
          cursor: pointer;
        }

        .loading {
          margin-left: 30px;
          margin-top: 0.25rem;
        }

        .margin-left-30 {
          margin-left: 30px;
        }

        .height-100 {
          height: 100%;
        }

        .grey-text {
          color: grey;
          margin-top: 0.5rem;
        }
      `}</style>
    </div>
  );
};

export default sideBarContent;
