import { Tree } from "antd";

import type { TreeProps } from "antd/es/tree";
import type { ListNodeData } from "../studio/EditableTree";

type TreeNodeData = {
  key: string;
  title: string;
  children: TreeNodeData[];
  level: number;
};

function tocGetTreeData(listData: ListNodeData[], active = "") {
  let treeData = [];
  let tocActivePath: TreeNodeData[] = [];
  let treeParents = [];
  let rootNode: TreeNodeData = {
    key: "0",
    title: "root",
    level: 0,
    children: [],
  };
  treeData.push(rootNode);
  let lastInsNode: TreeNodeData = rootNode;

  let iCurrLevel = 0;
  for (let index = 0; index < listData.length; index++) {
    const element = listData[index];

    let newNode: TreeNodeData = {
      key: element.key,
      title: element.title,
      children: [],
      level: element.level,
    };
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

    if (active === element.key) {
      tocActivePath = [];
      for (let index = 1; index < treeParents.length; index++) {
        //treeParents[index]["expanded"] = true;
        tocActivePath.push(treeParents[index]);
      }
    }
  }
  return treeData[0].children;
}

type IWidgetTocTree = {
  treeData: ListNodeData[];
};
const onSelect: TreeProps["onSelect"] = (selectedKeys, info) => {
  //let aaa: NewTree = info.node;
  console.log("selected", selectedKeys);
};
const Widget = ({ treeData }: IWidgetTocTree) => {
  const data = tocGetTreeData(treeData);

  //const [expandedKeys] = useState(["0-0", "0-0-0", "0-0-0-0"]);

  return (
    <>
      <Tree onSelect={onSelect} treeData={data} />
    </>
  );
};

export default Widget;
