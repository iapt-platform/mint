interface IWidget {
  size?: number;
}
const FileSizeWidget = ({ size = 0 }: IWidget) => {
  let strSize = 0;
  let end = "";
  if (size > Math.pow(1024, 3)) {
    strSize = size / Math.pow(1024, 3);
    end = "GB";
  } else if (size > Math.pow(1024, 2)) {
    strSize = size / Math.pow(1024, 2);
    end = "MB";
  } else if (size > Math.pow(1024, 1)) {
    strSize = size / Math.pow(1024, 1);
    end = "KB";
  } else {
    strSize = size;
    end = "B";
  }
  const output = strSize.toString().substring(0, 4) + end;
  return <>{output}</>;
};

export default FileSizeWidget;
