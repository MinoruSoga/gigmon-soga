// scripts.js

const loadingIndicator = document.querySelector(".loading-indicator");
const menuItems = document.querySelectorAll(".menu-item");
let systemFlag = false;

function markdownToHtml(markdownText) {
  return marked.parse(markdownText);
}

function h(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

function resetChat() {
  const messages = document.querySelector(".messages");
  messages.innerHTML = "";
}

// メニュー項目がクリックされたときの処理
function onMenuItemClick(event) {
  const messageText = event.target.getAttribute("data-message");
  const inputMessage = document.querySelector(".input-message");
  inputMessage.value = messageText;
  systemFlag = true;
}

// 各メニュー項目にクリックイベントリスナを追加
menuItems.forEach((menuItem) => {
  menuItem.addEventListener("click", onMenuItemClick);
});

// ローディングインジケータの表示・非表示を切り替える関数
function toggleLoadingIndicator(show) {
  loadingIndicator.style.display = show ? "flex" : "none";
}

function getConversationId(n) {
  var S = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  var N = 16;
  return Array.from(crypto.getRandomValues(new Uint8Array(N)))
    .map((n) => S[n % S.length])
    .join("");
}

document.addEventListener("DOMContentLoaded", () => {
  const sendMessageButton = document.querySelector(".send-message");
  const inputMessage = document.querySelector(".input-message");
  const conversationId = document.querySelector(".conversation-id");
  const messages = document.querySelector(".messages");
  const chatModeSelector = document.querySelector("#chat-mode");

  if (conversationId) {
    conversationId.value = getConversationId(32);
  }

  // APIへのリクエストを行う関数
  async function fetchResponse(messageText) {
    const apiUrlBase = "https://us-central1-gptworks.cloudfunctions.net/";
    const mode = chatModeSelector.value;
    let apiUrl = apiUrlBase + "chatGptApi";
    if (mode == "gigmon") {
      apiUrl = apiUrlBase + "docsBotApi";
    }
    const data = {
      message: messageText,
      conversationId: conversationId.value,
      systemFlag: systemFlag,
    };
    systemFlag = false;

    const response = await fetch(apiUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });

    if (response.ok) {
      const responseData = await response.json();
      return responseData.reply;
    } else {
      console.error("API request error:", response.statusText);
      return null;
    }
  }

  // メッセージ送信時の処理
  async function sendMessage() {
    const messageText = inputMessage.value.trim();

    if (messageText.length > 0) {
      const userMessageElement = document.createElement("div");
      userMessageElement.classList.add("message", "user-message");
      userMessageElement.innerHTML = markdownToHtml(h(messageText)).replace(
        /\n/g,
        "<br>"
      );
      messages.appendChild(userMessageElement);

      // 入力欄をクリアし、スクロールを最下部に移動
      inputMessage.value = "";
      messages.scrollTop = messages.scrollHeight;

      // ローディングインジケータを表示
      toggleLoadingIndicator(true);

      // APIからの返答を取得
      const replyText = await fetchResponse(messageText);

      // ローディングインジケータを非表示
      toggleLoadingIndicator(false);

      if (replyText) {
        const replyMessageElement = document.createElement("div");
        replyMessageElement.classList.add("message", "reply-message");
        replyMessageElement.innerHTML = markdownToHtml(h(replyText)).replace(
          /\n/g,
          "<br>"
        ); // 改行文字を<br>に置き換える
        messages.appendChild(replyMessageElement);

        // スクロールを最下部に移動
        messages.scrollTop = messages.scrollHeight;
      }
    }
  }

  // 送信ボタンがクリックされたとき
  if (sendMessageButton) {
    sendMessageButton.addEventListener("click", sendMessage);
  }

  // エンターキーが押されたとき
  if (inputMessage) {
    inputMessage.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        if (event.shiftKey) {
          return;
        }
        if (!event.isComposing) {
          event.preventDefault(); // デフォルトのEnterキーの動作を無効化
          sendMessage();
        }
      }
    });
  }

  // チャットモードが変更された時
  if (chatModeSelector) {
    chatModeSelector.addEventListener("change", () => {
      // チャット画面をリセット
      resetChat();
    });
  }
});
