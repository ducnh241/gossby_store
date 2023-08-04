-- Lưu lại id của design gốc được clone từ Personalized Design -> Personalized Design Amazion
ALTER TABLE osc_personalized_design ADD COLUMN design_cloned_id INT NULL DEFAULT NULL AFTER member_id;