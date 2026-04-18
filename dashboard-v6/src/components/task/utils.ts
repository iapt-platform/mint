import type { ITaskData } from "../../api/task";

// 更新 ITaskData[] 中的函数
export function update(input: ITaskData[], target: ITaskData[]): void {
  for (const newItem of input) {
    const match = target.findIndex((item) => item.id === newItem.id);
    if (match >= 0) {
      // 更新当前项的属性
      target[match] = newItem;
    } else {
      // 如果没有找到，递归检查子项
      for (const item of target) {
        if (item.children) {
          update([newItem], item.children);
        }
      }
    }
  }
}
// 更新函数
export function updateNode(tree: ITaskData[], changed: ITaskData): boolean {
  for (let i = 0; i < tree.length; i++) {
    if (tree[i].id === changed.id) {
      tree[i] = { ...tree[i], ...changed };
      return true;
    }
    if (tree[i].children) {
      const updated = updateNode(tree[i].children!, changed);
      if (updated) {
        console.debug("TaskList children", tree[i].children);
        return true;
      }
    }
  }
  return false;
}

export const treeToList = (tree: readonly ITaskData[]): ITaskData[] => {
  const output: ITaskData[] = [];
  const scan = (value: ITaskData) => {
    value.children?.forEach(scan);
    value.children = undefined;
    if (value.type !== "group") {
      output.push(value);
    }
  };
  tree.forEach(scan);
  return output;
};
