import React from "react";
const NoAccess = () => {
  return (
    <div
      style={{
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        height: "100vh",
        textAlign: "center",
        padding: "0 20px",
      }}
    >
      <p>
        アカウントが無効になっているため、チャットページにアクセスできません。支払い方法を登録して、プランを選択してからアクセスができるようになります。
      </p>
    </div>
  );
};

export default NoAccess;
