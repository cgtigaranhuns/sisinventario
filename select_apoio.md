SELECT b.rp, b.descricao, u.name AS conferido_por
FROM bens b
JOIN conferencias c ON b.id = c.rp_id
LEFT JOIN users u ON c.conferido_por_id = u.id
WHERE c.conferido_em IS NOT NULL 
  AND c.situacao IS NULL;