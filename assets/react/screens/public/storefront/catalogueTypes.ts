export type StoreCategory = {
  id: string;
  name: string;
  slug: string;
  parentId: string | null;
  count: number;
  image: string;
  banner?: string | null;
  description?: string | null;
  children: StoreCategory[];
};

export type StoreFilterValue = {
  id: string;
  value: string;
};

export type StoreFilter = {
  id: string;
  name: string;
  slug: string;
  type: string;
  position: number;
  values: StoreFilterValue[];
};
