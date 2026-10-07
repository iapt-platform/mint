import { useEffect, useLayoutEffect, useRef, useState } from "react";
import "./style.css";
import TermTextAreaMenu from "./TermTextAreaMenu";

interface IWidget {
  value?: string;
  menuOptions?: string[];
  placeholder?: string;
  onSave?: (value?: string) => void;
  onClose?: () => void;
  onChange?: (newValue: string) => void;
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
  /** 光标位置，菜单实际坐标由它再做边界钳制 */
  const [cursorPos, setCursorPos] = useState({ top: 0, left: 0 });
  const [menuItemCount, setMenuItemCount] = useState(0);
  const [menuSelected, setMenuSelected] = useState<string>();

  const [textAreaValue, setTextAreaValue] = useState(value);
  const [textAreaHeight, setTextAreaHeight] = useState(100);
  const [termSearch, setTermSearch] = useState<string>();

  const refTextArea = useRef<HTMLTextAreaElement>(null);
  const refShadow = useRef<HTMLDivElement>(null);
  const refMenu = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!refTextArea.current) return;

    const el = refTextArea.current;

    const observer = new ResizeObserver(() => {
      setShadowHeight(el.clientHeight);
    });

    observer.observe(el);

    return () => observer.disconnect();
  }, []);

  /**
   * 菜单显示后按容器和视口做边界钳制，避免超出可视区域
   */
  useLayoutEffect(() => {
    if (menuDisplay !== "block" || !refMenu.current || !refTextArea.current) {
      return;
    }
    const menu = refMenu.current;
    const container = refTextArea.current;
    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;

    let left = cursorPos.left;
    const maxLeft = container.clientWidth - menuWidth;
    if (left > maxLeft) {
      left = maxLeft;
    }
    if (left < 0) {
      left = 0;
    }

    let top = cursorPos.top + 20;
    // 菜单底部若超出视口，改为显示在光标上方
    const containerTop = container.getBoundingClientRect().top;
    if (containerTop + top + menuHeight > window.innerHeight) {
      const above = cursorPos.top - menuHeight;
      if (containerTop + above > 0) {
        top = above;
      }
    }

    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
  }, [menuDisplay, cursorPos, termSearch]);

  function term_at_menu_hide() {
    setMenuDisplay("none");
    setTermSearch("");
  }

  function termInsert(strTerm: string) {
    if (refTextArea.current === null) {
      return;
    }
    const value = refTextArea.current.value;
    const selectionStart = refTextArea.current.selectionStart;
    let str1 = value.slice(0, selectionStart);
    const str2 = value.slice(selectionStart);
    const pos1 = str1.lastIndexOf("[[");
    const pos2 = str1.lastIndexOf("]]");
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
        ref={refMenu}
        className="menu"
        style={{
          display: menuDisplay,
          top: cursorPos.top + 20,
          left: cursorPos.left,
        }}
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
          onCount={setMenuItemCount}
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
        onKeyDown={(event: React.KeyboardEvent<HTMLTextAreaElement>) => {
          switch (event.key) {
            case "ArrowDown":
              if (menuDisplay === "block") {
                if (menuFocusIndex < menuItemCount - 1) {
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
                if (onSave) {
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
        onKeyUp={() => {
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

          const value = refTextArea.current.value;
          const selectionStart = refTextArea.current.selectionStart;
          const str1 = value.slice(0, selectionStart);
          const str2 = value.slice(selectionStart);
          const textNode1 = document.createTextNode(str1);
          const textNode2 = document.createTextNode(str2);
          const cursor = document.createElement("span");
          cursor.innerHTML = "&nbsp;";
          cursor.setAttribute("class", "cursor");
          const mirror =
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
              setCursorPos({ top: cursor.offsetTop, left: cursor.offsetLeft });
              //menu.innerHTML = TermAtRenderMenu({ focus: 0 });
              //term_at_menu_show(cursor);
            }
          } else {
            if (menuDisplay === "block") {
              const pos1 = str1.lastIndexOf("[[");
              const pos2 = str1.lastIndexOf("]]");
              if (pos1 === -1 || (pos1 !== -1 && pos2 > pos1)) {
                //光标前没有[[ 或 光标在[[]] 之后
                setMenuDisplay("none");
                setTermSearch("");
              }
            }
          }

          if (menuDisplay === "block") {
            const value = refTextArea.current.value;
            const selectionStart = refTextArea.current.selectionStart;
            const str1 = value.slice(0, selectionStart);
            const pos1 = str1.lastIndexOf("[[");
            const pos2 = str1.lastIndexOf("]]");
            if (pos1 !== -1) {
              if (pos2 === -1 || pos2 < pos1) {
                //光标
                const term_input = str1.slice(str1.lastIndexOf("[[") + 2);
                if (term_input !== termSearch) {
                  //候选列表变了，焦点回到第一项
                  setMenuFocusIndex(0);
                  setTermSearch(term_input);
                }
              }
            }
          }
        }}
      />
    </div>
  );
};

export default TermTextAreaWidget;
