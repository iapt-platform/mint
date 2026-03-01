import type React from "react";

interface IWidget {
  header?: React.ReactNode;
  action?: React.ReactNode;
}
const ArticleHeader = ({ header, action }: IWidget) => {
  return (
    <div style={{ display: "flex", justifyContent: "space-between" }}>
      <div>{header}</div>
      {/**action */}
      <div>{action}</div>
    </div>
  );
};

export default ArticleHeader;
