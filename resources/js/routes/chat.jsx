import React, { useState, useEffect, useRef } from "react";
import axios from "axios";
import CryptoJS from "crypto-js";

import SideBar from "../components/ReactChat/SideBar";
import ChatContent from "../components/ReactChat/ChatContent";
import PromptContent from "../components/ReactChat/PromptContent";
import SpeedMeter from "../components/ReactChat/SpeedMeter";
import SettingsTray from "../components/ReactChat/SettingsTray";

const STREAM_ENABLED = true;

export default function Chat(props) {
  const csrfToken = props.token; // csrfトークン用
  const chatPanelRef = useRef(null); // チャットパネルのスクロール位置参照用

  const [selectedMode, setSelectedMode] = useState("ChatGpt"); // ChatGpt || knowledge
  const [selectedFunction, setSelectedFunction] = useState("none"); // none || function 1
  const [selectedModel, setSelectedModel] = useState("gpt-4-turbo"); //gpt-3.5-turbo || gpt-4

  const [activePanel, setActivePanel] = useState("chat"); //chat || generalPrompt || individualPrompt
  const [activeChat, setActiveChat] = useState(null);
  const [activePrompt, setActivePrompt] = useState(null); //object with title and content

  const [inputMessage, setInputMessage] = useState("");
  const [chatHistory, setChatHistory] = useState([]);
  const [conversation, setConversation] = useState([]); // TODO: can we get rid of this?

  const [isLoading, setIsLoading] = useState(false);

  const submitMessage = (prompt = false) => {
    if (inputMessage) {
      setIsLoading(true);

      //scroll up one pixel, hack for ios safari keyboard dissapearing but chat panel not expanding
      window.scrollBy(0, -1);

      const checkMessageHeaders = {
        "Content-Type": "application/json",
        "X-Csrf-Token": csrfToken,
      };

      // Remove spaces and new lines from both inputMessage and editedItem.description
      const inputMessageHash = CryptoJS.SHA256(
        inputMessage.replace(/\s/g, "")
      ).toString();
      const activePromptHash = CryptoJS.SHA256(
        activePrompt?.content?.replace(/\s/g, "")
      ).toString();

      if (inputMessageHash === activePromptHash) {
        makeChatApiCall(inputMessage);
      } else {
        axios
          .post(
            "/api/checkMessage",
            { message: inputMessage },
            { headers: checkMessageHeaders }
          )
          .then((response) => {
            setInputMessage("");
            const { status } = response.data;
            if (status === "OK") {
              makeChatApiCall(inputMessage);
            } else if (
              status === "Pending" &&
              window.confirm(
                "個人情報が含まれている可能性がございます。本当に送信しますか？"
              )
            ) {
              makeChatApiCall(inputMessage);
            } else {
              setIsLoading(false);
              if (status === "NG") {
                alert(
                  "NGワードが含まれています。メッセージの内容をご確認ください。"
                );
              }
            }
          })
          .catch((error) => {
            console.log("API request error:", error);
            setIsLoading(false);
          });
      }
    } else if (prompt) {
      setIsLoading(true);
      makeChatApiCall(activePrompt?.content, true);
    } else {
      console.log(`no input message`, inputMessage);
    }
  };

  const makeChatApiCall = async (inputMessage, isSystem = false) => {
    let currentChat = activeChat;
    if (activeChat === null) {
      //new chat
      currentChat = {
        conversations: isSystem
          ? []
          : [{ role: "user", content: inputMessage }],
        function_id: selectedFunction === "function 1" ? 1 : 0,
        model:
          selectedModel === "gpt-3.5-turbo"
            ? "gpt-3.5-turbo"
            : selectedModel === "gpt-4"
            ? "gpt-4"
            : "gpt-4-turbo",
      };
      //add new chat to sidebar and set as current chat
      setChatHistory((prevState, props) => {
        return [currentChat, ...prevState];
      });
      setActiveChat(currentChat);
    }

    let url = "/api/callgpt";
    if (selectedMode === "knowledge") {
      url = "/api/calldocs";
    }
    if (STREAM_ENABLED) {
      url += `stream`;
    }
    const headers = {
      "Content-Type": "application/json",
      "X-Csrf-Token": csrfToken,
    };

    let data = {
      message: inputMessage,
      conversationToken: currentChat?.conversation_token,
      functionId: currentChat.function_id,
      model: currentChat.model,
      isSystem,
    };

    if (!isSystem) {
      setConversation((prevState, props) => {
        const updatedConversation = [
          ...prevState,
          {
            role: "user",
            content: inputMessage,
          },
        ];
        return updatedConversation;
      });
    }

    if (STREAM_ENABLED) {
      try {
        let answer = "";
        const response = await fetch(url, {
          headers,
          method: "POST",
          body: JSON.stringify(data),
        });
        setIsLoading(false);
        if (!response?.ok) {
          console.error(`Error: ${response?.statusText}`);
          return;
        }

        const reader = response.body?.getReader();
        if (!reader) {
          console.error("Error: fail to read data from response");
          return;
        }

        setConversation((prevState, props) => {
          const updatedConversation = [
            ...prevState,
            {
              role: "chatbot",
              content: "",
            },
          ];
          return updatedConversation;
        });
        while (true) {
          const { done, value } = await reader.read();
          if (done) {
            break;
          }

          const textDecoder = new TextDecoder("utf-8");
          const text = textDecoder.decode(value, { stream: true });

          answer += text;

          const response = {
            role: "chatbot",
            content: answer,
          };

          setConversation((prevState, props) => {
            const updatedConversation = [...prevState];
            updatedConversation[updatedConversation.length - 1] = response;
            return updatedConversation;
          });
        }
        setInputMessage("");
        setActivePrompt(null);
      } catch (e) {
        console.log("API request error:", e);
        setIsLoading(false);
        let errorResponse = {
          role: "chatbot",
          content:
            "APIタイムアウトが発生しました。時間をおいて実行してください",
        };
        setConversation((prevState, props) => {
          const updatedConversation = [...prevState];
          updatedConversation.push(errorResponse);
          return updatedConversation;
        });
      } finally {
        setIsLoading(false);
        //update sidebar chat if new chat
        if (!activeChat || activeChat.conversations.length === 0) {
          //fetch history to get conversation token
          await fetchHistory(true);
        } else {
          setConversation((prevState) => {
            const updatedConversation = [...prevState];
            setChatHistory((prevState) => {
              const activeChatIndex = prevState.findIndex((item) => {
                return (
                  item.conversation_token === currentChat?.conversation_token
                );
              });
              const updatedChats = [...prevState];
              currentChat.conversations = updatedConversation;
              updatedChats[activeChatIndex || 0] = currentChat;
              return updatedChats;
            });
            return updatedConversation;
          });
        }
      }
    } else {
      axios
        .post(url, data, { headers })
        .then((response) => {
          const {
            status,
            response: apiResponse,
            conversationToken: updatedConversationToken,
          } = response.data;

          if (status === "success") {
            setConversation((prevState, props) => {
              const updatedConversation = [
                ...prevState,
                {
                  role: "chatbot",
                  content: apiResponse,
                },
              ];
              return updatedConversation;
            });
            currentChat.conversation_token = updatedConversationToken;
          } else {
            console.error("API response error:", apiResponse);
            setConversation((prevState, props) => {
              const updatedConversation = [
                ...prevState,
                {
                  role: "chatbot",
                  content:
                    "APIタイムアウトが発生しました。時間をおいて実行してください",
                },
              ];
              return updatedConversation;
            });
            currentChat.conversation_token = updatedConversationToken;
          }
          setInputMessage("");
          setActivePrompt(null);
          setActiveChat(currentChat);
        })
        .catch((error) => {
          console.log("API request error:", error);
          setIsLoading(false);
        })
        .finally(() => {
          // ローディング状態を終了
          setIsLoading(false);
          //update chat history
          setConversation((prevState) => {
            const updatedConversation = [...prevState];
            setChatHistory((prevState) => {
              const activeChatIndex = prevState.findIndex((item) => {
                return (
                  item.conversation_token === currentChat?.conversation_token
                );
              });
              const updatedChats = [...prevState];
              currentChat.conversations = updatedConversation;
              updatedChats[activeChatIndex || 0] = currentChat;
              return updatedChats;
            });
            return updatedConversation;
          });
        });
    }
  };

  const fetchHistory = async (selectNewestChat = false) => {
    const mode = selectedMode === "knowledge" ? 2 : 1;
    try {
      if (!selectNewestChat) {
        //only show loading on initial load
        setIsLoading(true);
      }
      const response = await fetch(`/api/history/${mode}`, {
        method: "GET",
        headers: {
          "X-Csrf-Token": csrfToken,
        },
      });
      const data = await response.json();
      setChatHistory(data);
      setIsLoading(false);
      if (selectNewestChat) {
        setActiveChat(data[0]);
      }
      return data;
    } catch (error) {
      console.log("error");
      return null;
    }
  };

  const focusMessageForm = () => {
    //needs a timout for when activechannel needs to change first
    setTimeout(() => {
      let messageForm = document.getElementById("messageForm");
      if (messageForm) {
        messageForm.focus();
      }
    }, 100);
  };

  useEffect(async () => {
    setActivePanel("chat");
    setActiveChat(null);
    setInputMessage("");
    await fetchHistory();
  }, [selectedMode]);

  useEffect(() => {
    if (activeChat) {
      setActivePanel("chat");
    }
    focusMessageForm();
    setConversation(activeChat?.conversations || []);
  }, [activeChat]);

  useEffect(() => {
    if (!isLoading && chatPanelRef.current) {
      const scrollHeight = chatPanelRef.current.scrollHeight;
      chatPanelRef.current.scrollTop = scrollHeight;
    }
  }, [conversation, isLoading]);

  const sideBarContent = (device) => (
    <SideBar
      device={device}
      activePanel={activePanel}
      setActivePanel={setActivePanel}
      activeChat={activeChat}
      setActiveChat={setActiveChat}
      isLoading={isLoading}
      chatHistory={chatHistory}
      setChatHistory={setChatHistory}
      selectedMode={selectedMode}
      setInputMessage={setInputMessage}
      csrfToken={csrfToken}
    />
  );

  const mainContent = () => {
    return activePanel === "chat" ? (
      <ChatContent
        submitMessage={submitMessage}
        inputMessage={inputMessage}
        setInputMessage={setInputMessage}
        conversation={conversation}
        chatPanelRef={chatPanelRef}
        isLoading={isLoading}
      />
    ) : (
      <PromptContent
        activePrompt={activePrompt}
        activePanel={activePanel}
        setActivePanel={setActivePanel}
        setActiveChat={setActiveChat}
        setInputMessage={setInputMessage}
        csrfToken={csrfToken}
        setActivePrompt={setActivePrompt}
        submitMessage={submitMessage}
      />
    );
  };

  return (
    <div className="chat-container container-fluid">
      <SettingsTray
        csrfToken={csrfToken}
        activeChat={activeChat}
        activePanel={activePanel}
        selectedMode={selectedMode}
        selectedFunction={selectedFunction}
        selectedModel={selectedModel}
        setSelectedMode={setSelectedMode}
        setSelectedFunction={setSelectedFunction}
        setSelectedModel={setSelectedModel}
      />
      {/* sliding sidebar for mobile */}
      <div
        className="offcanvas offcanvas-start"
        tabIndex="-1"
        id="offcanvas"
        data-bs-keyboard="false"
        data-bs-backdrop="false"
      >
        <div className="offcanvas-header">
          <div className="w-100 d-flex justify-content-center">
            <SpeedMeter csrfToken={csrfToken} />
          </div>
          <button
            type="button"
            className="btn-close text-reset"
            style={{ marginLeft: "auto" }}
            data-bs-dismiss="offcanvas"
            aria-label="Close"
          ></button>
        </div>
        <div className="offcanvas-body px-0">{sideBarContent("mobile")}</div>
      </div>
      <div className="h-100 d-flex flex-nowrap">
        <div className="col-xl-3 col-lg-4 col-md-5 px-0 overflow-scroll d-none d-md-block">
          {/* fixed side bar for desktop */}
          {sideBarContent("desktop")}
        </div>
        <div
          className="col-xl-9 col-lg-8 col-md-7 col-12 px-0"
          style={{
            position: "relative",
            overflowY: isLoading ? "hidden" : "scroll",
          }}
        >
          {mainContent()}
        </div>
      </div>
    </div>
  );
}
