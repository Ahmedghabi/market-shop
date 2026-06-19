import { FormField, Select } from './FormField';
import { useBoutique } from '../hooks/useBoutique';

type BoutiqueFormSelectProps = {
  value: string;
  onChange: (value: string) => void;
};

export function BoutiqueFormSelect({ value, onChange }: BoutiqueFormSelectProps) {
  const { boutique, boutiques } = useBoutique();

  if (boutique) return null;

  return (
    <FormField label="Boutique" required hint="Obligatoire quand la vue globale Toutes les boutiques est active.">
      <Select required value={value} onChange={(event) => onChange(event.target.value)}>
        <option value="">Sélectionner une boutique</option>
        {boutiques.map((item) => (
          <option key={item.id} value={item.id}>{item.name}</option>
        ))}
      </Select>
    </FormField>
  );
}

export function resolveFormBoutiqueId(currentBoutiqueId: string | undefined, formBoutiqueId: string): string | null {
  return currentBoutiqueId ?? (formBoutiqueId || null);
}
