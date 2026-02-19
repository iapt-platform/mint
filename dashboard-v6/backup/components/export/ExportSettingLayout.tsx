interface IWidget {
  label?: string;
  content?: React.ReactNode;
}
const ExportSettingLayoutWidget = ({ label, content }: IWidget) => {
  return (
    <div
      style={{
        display: "flex",
        justifyContent: "space-between",
        marginBottom: 4,
      }}
    >
      <span>{label}</span>
      <span>{content}</span>
    </div>
  );
};

export default ExportSettingLayoutWidget;
