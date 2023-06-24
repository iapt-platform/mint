import { useEffect } from "react";

interface IWidget {
  items?: string[];
  searchKey?: string;
  maxItem?: number;
  visible?: boolean;
  currIndex?: number;
  onChange?: Function;
  onSelect?: Function;
}
const TermTextAreaMenuWidget = ({
  items,
  searchKey,
  maxItem = 10,
  visible = false,
  currIndex = 0,
  onChange,
  onSelect,
}: IWidget) => {
  console.log("currIndex", currIndex);
  const filteredItem = searchKey
    ? items?.filter((value) => value.slice(0, searchKey.length) === searchKey)
    : items;
  useEffect(() => {
    if (filteredItem && typeof onChange !== "undefined") {
      if (currIndex < filteredItem?.length) {
        onChange(filteredItem[currIndex]);
      } else {
        onChange(filteredItem[filteredItem.length - 1]);
      }
    }
  }, [currIndex]);

  if (visible) {
    return (
      <>
        <div className="term_at_menu_input" key="head">
          {searchKey}
          {"|"}
        </div>
        <ul className="term_at_menu_ul">
          {filteredItem?.map((item, index) => {
            if (index < maxItem) {
              return (
                <li
                  key={index}
                  className={index === currIndex ? "term_focus" : undefined}
                  onClick={() => {
                    if (typeof onSelect !== "undefined") {
                      onSelect(item);
                    }
                  }}
                >
                  {item}
                </li>
              );
            } else {
              return undefined;
            }
          })}
        </ul>
      </>
    );
  } else {
    return <></>;
  }
};

export default TermTextAreaMenuWidget;
