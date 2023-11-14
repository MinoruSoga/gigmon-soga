import React, {useRef} from "react";
import { mdiSend } from "@mdi/js";
import Icon from "@mdi/react";
import useAutosizeTextArea from "../../hooks/useAutosizeTextArea.jsx";

const SendMsgForm = (props) => {
  const { inputMessage, setInputMessage, submitMessage, isLoading } = props;
  const textAreaRef = useRef(null);
  
  useAutosizeTextArea(textAreaRef, inputMessage);

  return (
    <div className="chat-box-tray">
      <textarea
        className="px-4 py-2"
        type="text"
        style={{ minHeight: "67px", maxHeight: "55vh" }}
        placeholder="メッセージを入力してください"
        value={inputMessage}
        ref={textAreaRef}
        id="messageForm"
        onChange={(e) => setInputMessage(e.target.value)}
        onKeyDown={(e) => {
          if (
            (e.ctrlKey && e.key === "Enter") ||
            (e.shiftKey && e.key === "Enter")
          ) {
            e.preventDefault();
            if(!isLoading){
              submitMessage();
            }
          }
        }}
      />
      <div className="send-btn">
        <Icon
          path={mdiSend}
          size={1.3}
          onClick={() => {
            if(!isLoading){
              submitMessage();
            }
          }}
        />
      </div>
    </div>
  );
};

export default SendMsgForm;
