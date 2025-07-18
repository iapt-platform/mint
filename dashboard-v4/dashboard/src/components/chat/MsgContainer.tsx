interface IWidget {
  children?: React.ReactNode;
}
const MsgContainer = ({ children }: IWidget) => {
  return (
    <div
      style={{
        display: "flex",
        justifyContent: "flex-start",
      }}
    >
      <div
        style={{
          maxWidth: "95%",
          color: "black",
          borderRadius: "8px",
          padding: "16px",
          border: "none",
          boxShadow: "0 1px 2px rgba(0, 0, 0, 0.03)",
          textAlign: "left",
        }}
      >
        {children}
      </div>
    </div>
  );
};

export default MsgContainer;
