import React from "react";

import ParserError from "../general/ParserError";
import type { TCodeConvertor } from "../../types/template";
import { my_to_roman, roman_to_my } from "../../utils/code/my";
import { roman_to_si } from "../../utils/code/si";
import { roman_to_thai } from "../../utils/code/thai";
import { roman_to_taitham } from "../../utils/code/tai-tham";
import { WdCtl } from "./Wd";
import MdTpl from "./MdTpl";

export function XmlToReact(
  text: string,
  wordWidget: boolean = false,
  convertor?: TCodeConvertor
): React.ReactNode[] | undefined {
  //console.log("html string:", text);
  const parser = new DOMParser();
  const xmlDoc = parser.parseFromString(`<body>${text}</body>`, "text/html");
  const x = xmlDoc.documentElement;
  //console.log("解析成功", x);
  return convert(x.getElementsByTagName("body")[0], wordWidget, convertor);

  function getAttr(node: ChildNode, key: number): object {
    const ele = node as Element;
    const attr = ele.attributes;
    //TODO fix it
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const output: any = { key: key };
    for (let i = 0; i < attr.length; i++) {
      if (attr[i].nodeType === 2) {
        const key: string = attr[i].nodeName;
        if (key !== "style") {
          if (key === "class") {
            output["className"] = attr[i].nodeValue;
          } else {
            output[key] = attr[i].nodeValue;
          }
        } else {
          //TODO 把css style 转换为react style
        }
      }
    }
    return output;
  }

  function convert(
    node: ChildNode,
    wordWidget: boolean = false,
    convertor?: TCodeConvertor
  ): React.ReactNode[] | undefined {
    const output: React.ReactNode[] = [];
    for (let i = 0; i < node.childNodes.length; i++) {
      const value = node.childNodes[i];
      //console.log(value.nodeName, value.nodeType, value.nodeValue);

      switch (value.nodeType) {
        case 1: {
          //element node
          const tagName = value.nodeName.toLowerCase();
          //console.log("tag", value.nodeName, tagName);
          switch (tagName) {
            case "parsererror":
              output.push(
                React.createElement(
                  ParserError,
                  getAttr(value, i),
                  convert(value, wordWidget, convertor)
                )
              );
              break;
            case "mdtpl":
              output.push(
                React.createElement(
                  MdTpl,
                  getAttr(value, i),
                  convert(value, wordWidget, convertor)
                )
              );
              break;
            case "param":
              output.push(
                React.createElement(
                  "span",
                  getAttr(value, i),
                  convert(value, wordWidget, convertor)
                )
              );
              break;
            default:
              try {
                output.push(
                  React.createElement(
                    tagName,
                    getAttr(value, i),
                    convert(value, wordWidget, convertor)
                  )
                );
              } catch (error) {
                console.log("ParserError", tagName, error);
                output.push(React.createElement(ParserError, { key: i }, []));
              }

              break;
          }

          break;
        }
        case 2: //attribute node
          return [];
        case 3: {
          //text node
          let textValue = value.nodeValue ? value.nodeValue : undefined;
          //编码转换
          if (typeof convertor !== "undefined" && textValue !== null) {
            switch (convertor) {
              case "roman_to_my":
                textValue = roman_to_my(textValue);
                break;
              case "my_to_roman":
                textValue = my_to_roman(textValue);
                break;
              case "roman_to_si":
                textValue = roman_to_si(textValue);
                break;
              case "roman_to_thai":
                textValue = roman_to_thai(textValue);
                break;
              case "roman_to_taitham":
                textValue = roman_to_taitham(textValue);
                break;
            }
          }
          if (wordWidget) {
            //将单词按照空格拆开。用组件包裹
            const wordList = textValue?.split(" ");
            const wordWidget = wordList?.map((word, id) => {
              // eslint-disable-next-line @typescript-eslint/no-explicit-any
              const prop: any = { key: id, text: word };
              return React.createElement(WdCtl, prop);
            });
            output.push(wordWidget);
          } else {
            output.push(textValue);
          }

          break;
        }
        case 8:
          return [];
        case 9:
          return [];
      }
    }
    if (output.length > 0) {
      return output;
    } else {
      return undefined;
    }
  }
}
