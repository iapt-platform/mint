import type { ReactNode } from "react";

interface IWidget {
  children?: ReactNode | ReactNode[];
}
const CommentaryPad = ({ children }: IWidget) => {
  return (
    <div
      style={{
        border: "2px dotted darkred",
        borderRadius: 8,
        padding: 4,
        margin: 6,
        backgroundColor: "#f5deb357",
      }}
    >
      {children}
    </div>
  );
};

export default CommentaryPad;
