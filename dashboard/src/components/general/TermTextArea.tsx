import { useRef, useState } from "react";
import "./style.css";
import TermTextAreaMenu from "./TermTextAreaMenu";

interface IWidget {
  value?: string;
  menuOptions?: string[];
  placeholder?: string;
  onSave?: Function;
  onClose?: Function;
  onChange?: Function;
}
const TermTextAreaWidget = ({
  value,
  menuOptions,
  placeholder,
  onSave,
  onClose,
  onChange,
}: IWidget) => {
  const [shadowHeight, setShadowHeight] = useState<number>();
  const [menuFocusIndex, setMenuFocusIndex] = useState(0);
  const [menuDisplay, setMenuDisplay] = useState("none");
  const [menuTop, setMenuTop] = useState(0);
  const [menuLeft, setMenuLeft] = useState(0);
  const [menuSelected, setMenuSelected] = useState<string>();

  const [textAreaValue, setTextAreaValue] = useState(value);
  const [textAreaHeight, setTextAreaHeight] = useState(100);
  const [termSearch, setTermSearch] = useState<string>();

  const _term_max_menu = 10;

  const refTextArea = useRef<HTMLTextAreaElement>(null);
  const refShadow = useRef<HTMLDivElement>(null);

  console.log("render");

  function term_at_menu_hide() {
    setMenuDisplay("none");
    setTermSearch("");
  }

  function termInsert(strTerm: string) {
    if (refTextArea.current === null) {
      return;
    }
    let value = refTextArea.current.value;
    let selectionStart = refTextArea.current.selectionStart;
    let str1 = value.slice(0, selectionStart);
    let str2 = value.slice(selectionStart);
    let pos1 = str1.lastIndexOf("[[");
    let pos2 = str1.lastIndexOf("]]");
    if (pos1 !== -1) {
      //光标前有[[
      if (pos2 === -1 || pos2 < pos1) {
        //光标在[[之间]]
        str1 = str1.slice(0, str1.lastIndexOf("[[") + 2);
      }
    }
    //TODO 光标会跑到最下面
    const newValue = str1 + strTerm + "]]" + str2;
    refTextArea.current.value = newValue;
    setTextAreaValue(newValue);
    if (typeof onChange !== "undefined") {
      onChange(newValue);
    }
    term_at_menu_hide();
    refTextArea.current.focus();
  }
  return (
    <div className="text_input">
      <div
        className="menu"
        style={{ display: menuDisplay, top: menuTop, left: menuLeft }}
      >
        <TermTextAreaMenu
          currIndex={menuFocusIndex}
          items={menuOptions}
          visible={menuDisplay === "block"}
          searchKey={termSearch}
          onSelect={(value: string) => {
            termInsert(value);
          }}
          onChange={(value: string) => {
            setMenuSelected(value);
          }}
        />
      </div>
      <div
        ref={refShadow}
        className="textarea text_shadow"
        style={{ height: shadowHeight }}
      ></div>
      <textarea
        className="textarea tran_sent_textarea"
        ref={refTextArea}
        style={{ height: textAreaHeight }}
        placeholder={placeholder}
        value={textAreaValue}
        onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
          setTextAreaValue(event.target.value);
          if (typeof onChange !== "undefined") {
            onChange(event.target.value);
          }
        }}
        onResize={(event) => {
          setShadowHeight(refTextArea.current?.clientHeight);
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLTextAreaElement>) => {
          switch (event.key) {
            case "ArrowDown":
              if (menuDisplay === "block") {
                if (menuFocusIndex < _term_max_menu) {
                  setMenuFocusIndex((value) => ++value);
                }
                event.preventDefault();
              }
              break;
            case "ArrowUp":
              if (menuDisplay === "block") {
                if (menuFocusIndex > 0) {
                  setMenuFocusIndex((value) => --value);
                }
                event.preventDefault();
              }
              break;
            case "Enter":
              if (menuDisplay === "block") {
                console.log("enter", menuSelected);
                if (menuSelected) {
                  termInsert(menuSelected);
                }
                setMenuDisplay("none");
                event.preventDefault();
              }
              if (event.ctrlKey || event.metaKey) {
                //回车存盘
                console.log("save", textAreaValue);
                if (typeof onSave !== "undefined") {
                  onSave(textAreaValue);
                }
              }
              break;
            case "Escape":
              if (menuDisplay === "block") {
                setMenuDisplay("none");
              } else {
                if (typeof onClose !== "undefined") {
                  onClose();
                }
              }
              break;
            default:
              break;
          }
        }}
        onKeyUp={(event) => {
          if (
            refShadow.current === null ||
            refTextArea.current === null ||
            refTextArea.current.parentElement === null
          ) {
            return;
          }
          let textHeight = refShadow.current.scrollHeight;
          const textHeight2 = refTextArea.current.clientHeight;
          if (textHeight2 > textHeight) {
            textHeight = textHeight2;
          }
          setTextAreaHeight(textHeight);

          let value = refTextArea.current.value;
          let selectionStart = refTextArea.current.selectionStart;
          let str1 = value.slice(0, selectionStart);
          let str2 = value.slice(selectionStart);
          let textNode1 = document.createTextNode(str1);
          let textNode2 = document.createTextNode(str2);
          let cursor = document.createElement("span");
          cursor.innerHTML = "&nbsp;";
          cursor.setAttribute("class", "cursor");
          let mirror =
            refTextArea.current.parentElement.querySelector(".text_shadow");
          if (mirror === null) {
            return;
          }
          mirror.innerHTML = "";
          mirror.appendChild(textNode1);
          mirror.appendChild(cursor);
          mirror.appendChild(textNode2);
          if (str1.slice(-2) === "[[") {
            if (menuDisplay !== "block") {
              setMenuFocusIndex(0);
              setMenuDisplay("block");
              setMenuTop(cursor.offsetTop + 20);
              setMenuLeft(cursor.offsetLeft);
              //menu.innerHTML = TermAtRenderMenu({ focus: 0 });
              //term_at_menu_show(cursor);
            }
          } else {
            if (menuDisplay === "block") {
              let pos1 = str1.lastIndexOf("[[");
              let pos2 = str1.lastIndexOf("]]");
              if (pos1 === -1 || (pos1 !== -1 && pos2 > pos1)) {
                //光标前没有[[ 或 光标在[[]] 之后
                setMenuDisplay("none");
                setTermSearch("");
              }
            }
          }

          if (menuDisplay === "block") {
            let value = refTextArea.current.value;
            let selectionStart = refTextArea.current.selectionStart;
            let str1 = value.slice(0, selectionStart);
            let pos1 = str1.lastIndexOf("[[");
            let pos2 = str1.lastIndexOf("]]");
            if (pos1 !== -1) {
              if (pos2 === -1 || pos2 < pos1) {
                //光标
                const term_input = str1.slice(str1.lastIndexOf("[[") + 2);
                setTermSearch(term_input);
              }
            }
          }
        }}
      />
    </div>
  );
};

export default TermTextAreaWidget;
