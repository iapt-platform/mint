import React from "react";
import MdTpl from "./MdTpl";

export function XmlToReact(text: string): React.ReactNode[] {
	//console.log("html string:", text);
	const parser = new DOMParser();
	const xmlDoc = parser.parseFromString(
		"<root><root>" + text + "</root></root>",
		"text/xml"
	);
	const x = xmlDoc.documentElement.childNodes;
	return convert(x[0]);

	function getAttr(node: ChildNode, key: number): Object {
		const ele = node as Element;
		const attr = ele.attributes;
		let output: any = { key: key };
		for (let i = 0; i < attr.length; i++) {
			if (attr[i].nodeType === 2) {
				let key: string = attr[i].nodeName;
				output[key] = attr[i].nodeValue;
			}
		}
		return output;
	}

	function convert(node: ChildNode): React.ReactNode[] {
		let output: React.ReactNode[] = [];

		for (let i = 0; i < node.childNodes.length; i++) {
			const value = node.childNodes[i];
			//console.log(value.nodeName, value.nodeType, value.nodeValue);

			switch (value.nodeType) {
				case 1: //element node
					switch (value.nodeName) {
						case "MdTpl":
							output.push(
								React.createElement(
									MdTpl,
									getAttr(value, i),
									convert(value)
								)
							);
							break;
						default:
							output.push(
								React.createElement(
									value.nodeName,
									getAttr(value, i),
									convert(value)
								)
							);
							break;
					}

					break;
				case 2: //attribute node
					return [];
				case 3: //text node
					output.push(value.nodeValue);
					break;
				case 8:
					return [];
				case 9:
					return [];
			}
		}
		return output;
	}
}
