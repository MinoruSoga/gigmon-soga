import React, { useEffect, useState } from "react";
import {
  mdiClockTimeFour,
  mdiPencil,
  mdiChartBar,
  mdiTextBoxEdit,
  mdiFaceMan,
  mdiCloud,
} from "@mdi/js";
import Icon from "@mdi/react";
import Modal from "../General/Modal";
import { useTranslation } from "react-i18next";

const PromptContent = (props) => {
  const {
    activePanel,
    setActivePanel,
    setActiveChat,
    setInputMessage,
    csrfToken,
    activePrompt,
    setActivePrompt,
    submitMessage,
  } = props;

  const { t } = useTranslation();
  const [categories, setCategories] = useState([]);
  const [selectedBoxId, setSelectedBoxId] = useState(null);
  const [allPrompts, setAllPrompts] = useState([]);
  const [filteredPrompts, setFilteredPrompts] = useState([]);
  const [isfetching, setIsfetching] = useState(false);
  const [showConfirmPromptModal, setConfirmPromptModal] = useState(false);

  const clickedPrompt = (prompt) => {
    setActivePrompt(prompt);
    if (activePanel === "generalPrompt") {
      //show modal to confirm prompt
      setConfirmPromptModal(true);
    }
    if (activePanel === "individualPrompt") {
      //open chat panel with pre-populated prompt
      setInputMessage(prompt.content);
      setActivePanel("chat");
      setActiveChat(null);
    }
  };

  const submitPrompt = () => {
    //open chat panel and auto submit prompt
    setActivePanel("chat");
    setActiveChat(null);
    submitMessage(activePrompt.content);
  };

  const getCategoryName = (categoryId) => {
    const category = categories.find((item) => item.id === categoryId);
    return category ? category.name : "";
  };

  const getCategoryItemCount = (categoryId) => {
    if (categoryId) {
      let count = allPrompts.filter(
        (item) => item.category_id === categoryId
      ).length;
      return count;
    } else {
      let count = allPrompts.length;
      return count;
    }
  };

  const filterPromptsByCategory = async (categoryId) => {
    if (isfetching) {
      return;
    }
    setSelectedBoxId(categoryId);

    if (categoryId) {
      const filtered = allPrompts.filter(
        (item) => item.category_id === categoryId
      );
      setFilteredPrompts(filtered);
    } else {
      setFilteredPrompts(allPrompts);
    }
  };

  const fetchPrompts = async () => {
    const type = activePanel.replace("Prompt", ""); //this will result in either general or individual
    setIsfetching(true);
    try {
      const response = await fetch(`/api/prompts/${type}/`, {
        method: "GET",
        headers: {
          "X-CSRF-TOKEN": csrfToken,
        },
      });
      const data = await response.json();
      setAllPrompts(data);
      setFilteredPrompts(data);
    } catch (error) {
      console.log("Error fetching items:", error);
    } finally {
      setIsfetching(false); // ローディングフラグをリセット
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await fetch("/api/categories", {
        method: "GET",
        headers: {
          "X-CSRF-TOKEN": csrfToken, // CSRFトークンを実際の値に置き換える
        },
      });
      const data = await response.json();
      setCategories(data);
    } catch (error) {
      console.log(error);
    }
  };

  useEffect(async () => {
    await fetchCategories();
  }, []); //leave dependence array empty, so it will only run once on mounted

  useEffect(async () => {
    if (activePanel === "generalPrompt" || activePanel === "individualPrompt") {
      await fetchPrompts();
    }
  }, [activePanel]);

  const categoryBoxes = [
    { id: 1, icon: mdiPencil },
    { id: 2, icon: mdiClockTimeFour },
    { id: 3, icon: mdiFaceMan },
    { id: 4, icon: mdiChartBar },
    { id: 5, icon: mdiTextBoxEdit },
    { id: 99, icon: mdiCloud },
  ];

  return (
    <div className="main-chat">
      <div className="prompt-panel container">
        {activePanel === "generalPrompt" && (
          <div className="box-container">
            {categoryBoxes.map((box, index) => (
              <div
                key={box.id}
                className={`box box-${box.id} ${
                  box.id === selectedBoxId ? "selected" : ""
                }`}
                onClick={() => filterPromptsByCategory(box.id)}
              >
                <div className="icon-container">
                  <Icon path={box.icon} size={1} />
                  <div className="category-name">{getCategoryName(box.id)}</div>
                </div>
                <div className="item-count">{getCategoryItemCount(box.id)}</div>
              </div>
            ))}
          </div>
        )}
        <div className="list-container">
          <div className="header">
            <div className="title">
              <p className="mb-0">{t("title")}</p>
            </div>
            {activePanel === "individualPrompt" && (
              <div className="description">
                <p className="mb-0">{t("content")}</p>
              </div>
            )}
          </div>
          {filteredPrompts.map((item, index) => (
            <div
              key={`item-${activePanel}-${index}`}
              className={`list-item ${index % 2 === 0 ? "" : "alt"}`}
              onClick={() => {
                clickedPrompt(item);
              }}
            >
              <div className="content-container">
                <div className="title">{item.title}</div>
                {item.content && activePanel === "individualPrompt" && (
                  <div className="description">{item.content}</div>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>
      <Modal
        showModal={showConfirmPromptModal}
        closeModal={() => {
          setConfirmPromptModal(false);
        }}
        title={`${activePrompt?.title || ''}${t("confirm_prompt_title")}`}
        buttonCta={t("submit")}
        buttonOnClick={submitPrompt}
        secondaryCta={t("cancel")}
        secondaryOnClick={() => {
          setConfirmPromptModal(false);
        }}
      />

      <style jsx>
        {`
          .main-chat {
            padding: 0;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            height: 100%;
          }

          .prompt-panel {
            height: 100%;
            display: flex;
            flex-direction: column;
          }

          .box-container {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
          }

          .list-container {
            flex-grow: 1;
            overflow: auto;
          }
          .header {
            font-weight: bold;
          }

          .header,
          .list-item {
            display: flex;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            background-color: #f5962f;
            cursor: pointer;
            border-radius: 5px;
          }

          .list-item {
            background-color: #fff;
            border: 1px solid #f5962f;
          }

          .list-item.alt {
            background-color: #fff;
          }

          .content-container {
            display: flex;
            width: 100%;
          }

          .title,
          .description {
            flex: 2;
            min-width: 0;
            text-align: left;
            white-space: pre-wrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 30%;
          }

          .description {
            flex: 4;
            width: "60%";
          }

          .box {
            border: none;
            padding: 0px;
            margin-right: 10px;
            height: 100px;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
            flex: 1 1 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 2px solid #f5962f;
            border-radius: 10px;
            background: white;
            color: #f5962f;
          }

          .box:hover,
          .box.selected {
            background: #f5962f;
            color: white;
            border-color: white;
          }

          .icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
            color: #f5962f;
            height: 40px;
          }

          .category-name {
            max-width: 100px;
            font-size: 0.8rem;
            margin-right: 10px;
            color: #f5962f;
          }

          .item-count {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #f5962f;
            color: #f5962f;
            margin-top: 10px;
          }

          .box:hover .icon-container,
          .box.selected .icon-container,
          .box:hover .category-name,
          .box.selected .category-name,
          .box:hover .item-count,
          .box.selected .item-count {
            color: white;
          }

          .box:hover .item-count,
          .box.selected .item-count {
            border-color: white;
          }
        `}
      </style>
    </div>
  );
};

export default PromptContent;
