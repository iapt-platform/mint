import type React from "react";
import styles from "../../general/SplitLayout/SplitLayout.module.css";

interface IWidget {
  header?: React.ReactNode;
  action?: React.ReactNode;
}
const ArticleHeader = ({ header, action }: IWidget) => {
  return (
    <div
      className={styles.sidebarHeader}
      style={{ display: "flex", justifyContent: "space-between" }}
    >
      <div>{header}</div>
      {/**action */}
      <div>{action}</div>
    </div>
  );
};

export default ArticleHeader;
