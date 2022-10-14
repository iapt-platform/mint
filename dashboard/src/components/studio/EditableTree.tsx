import React, { useState } from "react";
import { Tree } from "antd";
import type { DataNode, TreeProps } from "antd/es/tree";

type TreeNodeData = {
	key: string;
	title: string;
	children: TreeNodeData[];
	level: number;
};
export type ListNodeData = {
	article: string;
	title: string;
	level: number;
};

var tocActivePath: TreeNodeData[] = [];
function tocGetTreeData(articles: ListNodeData[], active = "") {
	let treeData = [];

	let treeParents = [];
	let rootNode: TreeNodeData = { key: "0", title: "root", level: 0, children: [] };
	treeData.push(rootNode);
	let lastInsNode: TreeNodeData = rootNode;

	let iCurrLevel = 0;
	for (let index = 0; index < articles.length; index++) {
		const element = articles[index];

		let newNode: TreeNodeData = { key: element.article, title: element.title, children: [], level: element.level };
		/*
		if (active == element.article) {
			newNode["extraClasses"] = "active";
		}
*/
		if (newNode.level > iCurrLevel) {
			//新的层级比较大，为上一个的子目录
			treeParents.push(lastInsNode);
			lastInsNode.children.push(newNode);
		} else if (newNode.level === iCurrLevel) {
			//目录层级相同，为平级
			treeParents[treeParents.length - 1].children.push(newNode);
		} else {
			// 小于 挂在上一个层级
			while (treeParents.length > 1) {
				treeParents.pop();
				if (treeParents[treeParents.length - 1].level < newNode.level) {
					break;
				}
			}
			treeParents[treeParents.length - 1].children.push(newNode);
		}
		lastInsNode = newNode;
		iCurrLevel = newNode.level;

		if (active === element.article) {
			tocActivePath = [];
			for (let index = 1; index < treeParents.length; index++) {
				//treeParents[index]["expanded"] = true;
				tocActivePath.push(treeParents[index]);
			}
		}
	}
	return treeData[0].children;
}

type IWidgetEditableTree = {
	treeData?: ListNodeData[];
};
const Widget = ({ treeData = [] }: IWidgetEditableTree) => {
	const data = tocGetTreeData(treeData);
	const [gData, setGData] = useState(data);
	const [expandedKeys] = useState(["0-0", "0-0-0", "0-0-0-0"]);

	const onDragEnter: TreeProps["onDragEnter"] = (info) => {
		console.log(info);
		// expandedKeys 需要受控时设置
		// setExpandedKeys(info.expandedKeys)
	};

	const onDrop: TreeProps["onDrop"] = (info) => {
		console.log(info);
		const dropKey = info.node.key;
		const dragKey = info.dragNode.key;
		const dropPos = info.node.pos.split("-");
		const dropPosition = info.dropPosition - Number(dropPos[dropPos.length - 1]);

		const loop = (
			data: DataNode[],
			key: React.Key,
			callback: (node: DataNode, i: number, data: DataNode[]) => void
		) => {
			for (let i = 0; i < data.length; i++) {
				if (data[i].key === key) {
					return callback(data[i], i, data);
				}
				if (data[i].children) {
					loop(data[i].children!, key, callback);
				}
			}
		};
		const data = [...gData];

		// Find dragObject
		let dragObj: DataNode;
		loop(data, dragKey, (item, index, arr) => {
			arr.splice(index, 1);
			dragObj = item;
		});

		if (!info.dropToGap) {
			// Drop on the content
			loop(data, dropKey, (item) => {
				item.children = item.children || [];
				// where to insert 示例添加到头部，可以是随意位置
				item.children.unshift(dragObj);
			});
		} else if (
			((info.node as any).props.children || []).length > 0 && // Has children
			(info.node as any).props.expanded && // Is expanded
			dropPosition === 1 // On the bottom gap
		) {
			loop(data, dropKey, (item) => {
				item.children = item.children || [];
				// where to insert 示例添加到头部，可以是随意位置
				item.children.unshift(dragObj);
				// in previous version, we use item.children.push(dragObj) to insert the
				// item to the tail of the children
			});
		} else {
			let ar: DataNode[] = [];
			let i: number;
			loop(data, dropKey, (_item, index, arr) => {
				ar = arr;
				i = index;
			});
			if (dropPosition === -1) {
				ar.splice(i!, 0, dragObj!);
			} else {
				ar.splice(i! + 1, 0, dragObj!);
			}
		}
		setGData(data);
		console.log(gData);
	};
	return (
		<Tree
			className="draggable-tree"
			defaultExpandedKeys={expandedKeys}
			draggable
			blockNode
			multiple
			onDragEnter={onDragEnter}
			onDrop={onDrop}
			treeData={gData}
		/>
	);
};

export default Widget;
