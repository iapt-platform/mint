import { Button, Tree } from "antd";
import { Key } from "antd/lib/table/interface";
import { useState } from "react";

interface DataNode {
  title: string;
  key: string;
  level?: number;
  children?: DataNode[];
  deletedAt?: string | null;
}

const Widget = () => {
  const [selectedKeys, setSelectedKeys] = useState<Key[]>();
  const [expandedKeys, setExpandedKeys] = useState<Key[]>();
  const [treeData, setTreeData] = useState<DataNode[]>([
    {
      key: "56b4a8e9-77b1-486c-96fc-28f352d8da47",
      title: "教程",
      children: [
        {
          key: "968b8cf0-cfb5-416d-81a5-edab88ad909a",
          title: "学习误区",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "9df0d8b2-0035-4ef1-b85c-1c464f28219e",
          title: "学习原则",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "1cbdaf6b-1e19-4d27-ba36-2babe402afec",
      title: "字母和发音",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "0eb7e7cd-31cd-40d6-b461-080d83730d85",
      title: "字母表",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "92fe7a62-6af0-4e31-b56a-eafd08033b7a",
      title: "发音技巧",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "f2a393e6-f7df-43e9-aeef-124eeec5f876",
      title: "名词变格手册",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "83a341ff-3066-46e3-8675-e056ca2cf313",
      title: "变格词概述",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "4950c3d6-798e-48fb-a8ae-009eb50f3896",
      title: "因果",
      children: [
        {
          key: "b1241559-e339-43f3-900a-8c4188dd698c",
          title: "名词",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "cf1df2d0-3b72-4c9a-ac23-73eadeccf3f0",
          title: "三性词",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "bb62fc57-4087-49a1-8f85-41b57d457f15",
      title: "变格表",
      children: [
        {
          key: "d4beb634-8a0e-4b28-88e6-9ae9f0d234d2",
          title: "new article",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "d06a6de5-d18f-4326-a77a-97f9e7269d5d",
          title: "传统语尾（1433）",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "cbaa8372-2aad-4c3b-b127-7d9b2686ebc4",
          title: "new article",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "bc857d97-3365-47d7-9726-b3db72dfea51",
          title: "元音结尾",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "22cb19f4-6299-46bd-af5f-4d6d8c109c25",
      title: "new article",
      children: [
        {
          key: "bcb2f86d-7de3-472c-bcdb-e573ff8f61fd",
          title: "a结尾中性（860）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "d8480939-ea56-44f7-b173-3ab91248f20d",
      title: "new article",
      children: [
        {
          key: "0dc41952-4ded-47d9-8fe2-1dbffd75c166",
          title: "ā结尾阴性（683）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "de2d5019-12b9-4294-be88-00f41385c3c7",
      title: "new article",
      children: [
        {
          key: "3151e372-5a89-4f7e-9954-1cdd484ca829",
          title: "i阳性（923）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "36858fe6-df6d-42fe-8779-de5a39e28d08",
      title: "new article",
      children: [
        {
          key: "7af620ee-3c91-4c1d-8160-367e97d814ca",
          title: "i中性（841）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "3d6e98e2-e1fe-4ba0-a3da-463f8e5f9e7a",
      title: "new article",
      children: [
        {
          key: "70f7bd0a-452a-4c09-b895-7d366fb7d37e",
          title: "i/ī阴性（1056）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "bd8ac4fc-b73c-4211-9228-bb5577287269",
      title: "new article",
      children: [
        {
          key: "f2192663-059c-4f12-9d32-dda6e755849c",
          title: "u阳性（889）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "3bfaa296-3af4-4af4-b3f3-56c6a1376edd",
      title: "new article",
      children: [
        {
          key: "d53cbc21-f6ad-4373-a4d7-e629d9bdbdcc",
          title: "u中性（857）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "ff312ec8-7742-439e-9e4b-abda816cfaba",
      title: "某些佛教术语",
      children: [
        {
          key: "318e69b1-bfa6-420c-a2f9-4d11b2c49019",
          title: "辅音结尾",
          children: [
            {
              key: "18277f2c-d21a-4669-ab0c-853ec1b1cc24",
              title: "ā/an阳性（2826）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "281e29ee-05c9-431b-925e-caea4eacd135",
              title: "ant阳性（2872）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "6525764f-a10a-4580-9800-0c38056eeefb",
              title: "ant中性（1177）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "bfac84ae-4e5f-4b3f-a9e0-f3e7a9c5b143",
              title: "ī/in阳性（548）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "880c641f-27b9-42dc-b3a5-3d528a224d3c",
              title: "ī/in中性（533）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "031957a0-f4ab-4271-b739-aee453031093",
              title: "o/as中性（空）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "fbbfa4f0-d1c6-4249-b319-a2b24da50402",
              title: "us（6，待补）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "f4fa473f-ff4e-4dc4-b158-d123d96d54e8",
              title: "ar阳性（668）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "d9705897-05f0-42b4-a04e-0044f71613f0",
              title: "ar阴性（1144）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "4f9493ba-a673-49b1-9b02-e48045d2c5e0",
          title: "代词",
          children: [
            {
              key: "f237d13f-144d-458e-81c7-7f5a1ba453c9",
              title: "第一人称（496）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "7d50ea56-555a-426d-8caa-64e2847d77fb",
              title: "第二人称（566）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "6eeb17bd-cd85-4fe7-b5ac-c6a0439f97a4",
              title: "第三人称",
              children: [
                {
                  key: "d7c12e07-d38b-420d-af5c-4099c42d2821",
                  title: "远指ta（1033）",
                  children: [],
                  level: 4,
                  deletedAt: null,
                },
                {
                  key: "8bac2320-91f1-4cca-8d6e-947993496430",
                  title: "远近指eta（1242）",
                  children: [],
                  level: 4,
                  deletedAt: null,
                },
                {
                  key: "7539f4bf-74a5-4d76-844a-f2a54630fccb",
                  title: "近指ima（1315）",
                  children: [],
                  level: 4,
                  deletedAt: null,
                },
                {
                  key: "c4d7bd3e-b1b1-4be4-b277-ec43efe2cc20",
                  title: "分句引导指代ya（1054）",
                  children: [],
                  level: 4,
                  deletedAt: null,
                },
              ],
              level: 3,
              deletedAt: null,
            },
            {
              key: "d0623f17-25f0-4c6b-b09c-74fa4df335a0",
              title: "疑问代词 Ka（1198）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "09ee9f07-0166-41f1-be6a-5fbdbe3967f5",
              title: "kaci（待替换）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "b1f79eaa-88d9-4365-b2b5-1fb62ca1b441",
              title: "代词-Sabba（819）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "77cc93cf-72ca-435a-9621-fcdc2caf894f",
          title: "数词（250）",
          children: [
            {
              key: "ea428c5b-e513-4718-b33e-63c71dabeb94",
              title: "基数词和序数词表（1647）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "eec6e8c6-8692-4d0a-b679-05631c762307",
              title: "eka一（647）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "23b35eeb-026f-4194-8365-ccfd1bd850da",
              title: "Dvi二（356）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "d5356104-57e5-456a-855c-fd834dc1389c",
              title: "Ubha二（空）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "800e4a18-4e73-4caa-9a97-175bc66c6ea8",
              title: "ti三（269，异常）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "d156c686-cc3a-41c6-87db-b9f568a40d43",
              title: "catu四（458）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "bb4a6b42-e393-4e90-91cb-81dc0168ef4f",
              title: "pañca五（空）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "0ce515c0-154f-4faf-844a-2bd100ff3db6",
          title: "格（1492）",
          children: [
            {
              key: "300245af-ca5d-4658-b76d-e52fd24fed11",
              title: "主格（1514）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "42a414ea-26cf-43cb-a938-6d87520a5e85",
              title: "呼格（1788）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "2eb35a25-7a76-4667-92ed-8776e13210dd",
              title: "宾格（4466）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "0e51f8e7-d75b-4a7e-a37f-c968a0949088",
              title: "工具格（3666）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "24ab52df-29d4-4313-9988-3828779eca79",
              title: "目的格（1427）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "70057e69-ca40-4d60-a1f0-b874499516df",
              title: "来源格（4603）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "31c25612-9a4b-4c12-bafb-00f2bdca83bd",
              title: "属格（3054）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "d519d2ee-8adc-406a-b43f-2e7b77f263fd",
              title: "位置格（1638）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "d55e0eea-5856-4621-a75c-0281bec7a1db",
          title: "数（432）",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "49d76ae6-2c28-42ef-8232-c29f43406b8c",
          title: "性（330）",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "0c942cd8-5067-4de8-8490-d9def3c1c6d2",
      title: "动词变位手册（1034）",
      children: [
        {
          key: "2beb4a80-3194-4a38-aa3f-12cfaa40c2e4",
          title: "动词变化总表（1011）",
          children: [],
          level: 2,
          deletedAt: null,
        },
        {
          key: "102a8854-4e86-4796-81b6-e73e95aec9ab",
          title: "变位动词·时态&语气（265）",
          children: [
            {
              key: "ae7638a6-b607-4eb5-a3bf-d792bc3b526e",
              title: "现在时（1490）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "edbdfa87-6286-4011-9640-fc787e7f0dd8",
              title: "不定过去（845）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "72860539-6a5c-43f2-94cb-59be27274a12",
              title: "将来时（1404）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "08c21337-2fdf-43a3-b3ad-bbb0b8f421bd",
              title: "命令式（1872）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "4775fa71-4de7-4b5f-9afb-b8ac29e86386",
              title: "潜能式（1396）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "e0e65c16-1ef2-4f75-95c9-d23a7f30a1d3",
              title: "条件式（73）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "a25bde48-f279-4919-98b1-4b0ff0e0ef3a",
          title: "三性动词·分词（317）",
          children: [
            {
              key: "3fe17ff1-dc63-4ca8-9e2d-e41a548245c0",
              title: "现在分词（1599）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "61acdd99-8964-4696-bfd6-c11750d4c452",
              title: "现在分词查词法（空）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "2dc29931-cf71-4185-8667-c4ee2e486bc8",
              title: "过去分词（2136）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "288db56e-c0ed-48f8-a9b0-5d412585dcbb",
              title: "过去分词查词法（空）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "1d22a40b-c61c-49ef-bb4c-2e0ffd06c757",
              title: "未来被动分词（1743，内部标题与前俩不一致）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "17a21585-a194-4b49-a48a-67d0f6a29c4b",
              title: "未来被动分词查词法（空）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "1cddba51-0049-4ecc-aa4f-8fbbc167ff68",
          title: "不变动词（空）",
          children: [
            {
              key: "bf52583b-d889-4785-b273-bcc2b75e5c09",
              title: "连续体（611）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "8277f0d1-38f8-4f99-8c0e-75a8b78e9518",
              title: "不定式（1190）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "8aaacbd9-32c2-4a4d-a7c2-0619e86c0889",
          title: "不规则动词（11，待补）",
          children: [
            {
              key: "1a8219e2-278c-4c93-8118-9b83c0a89e70",
              title: "atthi（407）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "06fb92cf-f57f-4906-ab5c-9d9f952df49a",
      title: "衍生（837）",
      children: [
        {
          key: "2dbd3c29-a2b7-498e-8a11-3c9930afbd57",
          title: "接头词",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "eb24a857-2ee6-4ef6-aa3c-1df32beafaa5",
      title: "复合词",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "cbfde201-3557-40b8-8008-0ef530f9cc51",
      title: "基本关系语法",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "7264128b-6fbd-4feb-a385-d9744049a022",
      title: "进阶关系语法",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "bc38b91f-0b00-4c8a-8ec0-c2fafe62cc21",
      title: "规范性表达",
      children: [],
      level: 1,
      deletedAt: null,
    },
    {
      key: "a9381b97-cd24-450e-8295-b0e932fb99d6",
      title: "句子关系（3844）",
      children: [
        {
          key: "ba114521-e5b9-4bf2-81a8-01956f4ef10b",
          title: "指代（660）",
          children: [
            {
              key: "61088ea0-f7a0-47cd-beeb-99023a2bd37a",
              title: "总-分（1010）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "41828fab-6785-48e3-b22c-e54af43a700d",
              title: "分-总（492）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "1cb32749-10cb-4541-9b11-52d7a6a53c87",
              title: "笼统-具体（815）",
              children: [],
              level: 3,
              deletedAt: null,
            },
            {
              key: "5e935881-56d7-4521-99d6-d450fb12cda2",
              title: "待定-确定（1212）",
              children: [],
              level: 3,
              deletedAt: null,
            },
          ],
          level: 2,
          deletedAt: null,
        },
        {
          key: "eb36c851-4e58-4040-ab1b-86d3d47f31d2",
          title: "其他",
          children: [],
          level: 2,
          deletedAt: null,
        },
      ],
      level: 1,
      deletedAt: null,
    },
    {
      key: "0d98aa11-c312-4d27-998a-543cd61514bf",
      title: "其他参考手册",
      children: [],
      level: 1,
      deletedAt: null,
    },
  ]);
  return (
    <>
      <Button
        onClick={() => {
          setTreeData((origin) => {
            return [...origin, { title: "title3", key: "title3" }];
          });
        }}
      >
        add
      </Button>
      <Button
        onClick={() => {
          setExpandedKeys(["title1"]);
          setSelectedKeys(["title1-2"]);
        }}
      >
        expand
      </Button>
      <Tree
        treeData={treeData}
        onSelect={(selectedKeys: Key[]) => setSelectedKeys(selectedKeys)}
      />
    </>
  );
};

export default Widget;
