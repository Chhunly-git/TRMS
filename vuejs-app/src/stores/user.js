import { defineStore } from 'pinia';

export const useUserStore = defineStore('user',
  {
    state: () => ({
      id: null,
      name: null,
      name_kh: null,
      name_en: null,
      email: null,
      profile_image: null,
      profile_thumbnail: null,
      password_null: true,
      level: null,
      status: null,
      employee_type: null,
      employee_code: null,
      mef_card_number: null,
      department_id: null,
      office_id: null,
      position_id: null,
      department: null,
      office: null,
      position: null,

      // ព័ត៌មានផ្ទាល់ខ្លួនបន្ថែម
      gender: null,
      dob: null,
      marital_status: null,
      birth_place: null,
      current_address: null,
      phone: null,
      national_id_number: null,
      national_id_expired_date: null,
      national_id_file: null,
      passport_number: null,
      passport_expired_date: null,
      passport_file: null,

      // កាលបរិច្ឆេទ និងប្រវត្តិការងារដំបូង
      first_service_date: null,
      first_appointment_date: null,
      initial_framework: null,
      initial_position: null,
      initial_ministry: null,
      initial_unit: null,
      initial_department: null,
      initial_office: null,

      // ស្ថានភាពមុខងារបច្ចុប្បន្ន
      officer_status: 'ACTIVE',
      officer_status_date: null,
      officer_status_reason: null,
      service_duration: null,
      service_duration_formatted: null,
      current_framework: null,
      current_appointment_date: null,
      current_position_date: null,

      // ៧. ស្ថានភាពគ្រួសារ
      father_name: null,
      father_latin_name: null,
      father_status: null,
      father_dob: null,
      father_nationality: null,
      father_address: null,
      father_occupation: null,
      father_unit: null,

      mother_name: null,
      mother_latin_name: null,
      mother_status: null,
      mother_dob: null,
      mother_nationality: null,
      mother_address: null,
      mother_occupation: null,
      mother_unit: null,

      spouse_name: null,
      spouse_latin_name: null,
      spouse_status: null,
      spouse_dob: null,
      spouse_nationality: null,
      spouse_birthplace: null,
      spouse_occupation: null,
      spouse_unit: null,
      spouse_allowance: null,
      spouse_phone: null,

      siblings: [],
      children: [],

      additional_positions: [],
      out_of_framework_statuses: [],
      unpaid_leaves: [],
      public_work_histories: [],
      private_work_histories: [],
      user_decorations: [],
      disciplinary_actions: [],
      educations: [],
      languages: [],
      permissions: [],
    }),
    getters: {
      isAuthenticated: (state) => !!state.id,
      // ពិនិត្យសិទ្ធិ Admin យ៉ាងមានសុវត្ថិភាព (មិនខ្វល់រឿងអក្សរតូច/ធំ)
      isAdmin: (state) => {
        if (!state.level) return false;
        return String(state.level).trim().toUpperCase() === 'ADMIN';
      },
      // ពិនិត្យសិទ្ធិលើមុខងារ ឬម៉ឺនុយនីមួយៗ
      can: (state) => (permissionKey) => {
        if (state.level && String(state.level).trim().toUpperCase() === 'ADMIN') {
          return true;
        }
        const defaultPerms = ['profile', 'my-attendances', 'document-templates', 'work-schedules'];
        if (!state.permissions || !Array.isArray(state.permissions) || state.permissions.length === 0) {
          return defaultPerms.includes(permissionKey);
        }
        return state.permissions.includes(permissionKey);
      },
      // ពិនិត្យថាតើមន្ត្រីមានសិទ្ធិលើម៉ឺនុយគ្រប់គ្រងណាមួយដែរឬទេ
      hasAnyAdminPermission: (state) => {
        if (state.level && String(state.level).trim().toUpperCase() === 'ADMIN') {
          return true;
        }
        const adminPerms = [
          'dashboard',
          'manage-document-templates',
          'users',
          'attendances',
          'departments',
          'divisions',
          'positions',
          'backups'
        ];
        if (!Array.isArray(state.permissions)) return false;
        return adminPerms.some(p => state.permissions.includes(p));
      }
    },
    actions: {
      // User state management
      setState(user) {
        this.id = user.id;
        this.name = user.name;
        this.name_kh = user.name_kh;
        this.name_en = user.name_en;
        this.email = user.email;
        this.profile_image = user.profile_image;
        this.profile_thumbnail = user.profile_thumbnail;
        this.password_null = user.password_null;
        
        // 🟢 កន្លែងដែលត្រូវ Assign level និង status
        this.level = user.level;
        this.status = user.status;
        if (user.level) {
          localStorage.setItem("user_level", user.level);
        }

        this.employee_type = user.employee_type;
        this.employee_code = user.employee_code;
        this.mef_card_number = user.mef_card_number;

        // ព័ត៌មានផ្ទាល់ខ្លួន
        this.gender = user.gender;
        this.dob = user.dob;
        this.marital_status = user.marital_status;
        this.birth_place = user.birth_place;
        this.current_address = user.current_address;
        this.phone = user.phone;
        this.national_id_number = user.national_id_number;
        this.national_id_expired_date = user.national_id_expired_date;
        this.national_id_file = user.national_id_file;
        this.passport_number = user.passport_number;
        this.passport_expired_date = user.passport_expired_date;
        this.passport_file = user.passport_file;

        // ស្ថានភាពការងារដំបូង
        this.first_service_date = user.first_service_date;
        this.first_appointment_date = user.first_appointment_date;
        this.initial_framework = user.initial_framework;
        this.initial_position = user.initial_position;
        this.initial_ministry = user.initial_ministry;
        this.initial_unit = user.initial_unit;
        this.initial_department = user.initial_department;
        this.initial_office = user.initial_office;

        // ស្ថានភាពការងារបច្ចុប្បន្ន
        this.officer_status = user.officer_status || 'ACTIVE';
        this.officer_status_date = user.officer_status_date;
        this.officer_status_reason = user.officer_status_reason;
        this.service_duration = user.service_duration;
        this.service_duration_formatted = user.service_duration_formatted;
        this.current_framework = user.current_framework;
        this.current_appointment_date = user.current_appointment_date;
        this.current_position_date = user.current_position_date;

        // ៧. ស្ថានភាពគ្រួសារ
        this.father_name = user.father_name;
        this.father_latin_name = user.father_latin_name;
        this.father_status = user.father_status;
        this.father_dob = user.father_dob;
        this.father_nationality = user.father_nationality;
        this.father_address = user.father_address;
        this.father_occupation = user.father_occupation;
        this.father_unit = user.father_unit;

        this.mother_name = user.mother_name;
        this.mother_latin_name = user.mother_latin_name;
        this.mother_status = user.mother_status;
        this.mother_dob = user.mother_dob;
        this.mother_nationality = user.mother_nationality;
        this.mother_address = user.mother_address;
        this.mother_occupation = user.mother_occupation;
        this.mother_unit = user.mother_unit;

        this.spouse_name = user.spouse_name;
        this.spouse_latin_name = user.spouse_latin_name;
        this.spouse_status = user.spouse_status;
        this.spouse_dob = user.spouse_dob;
        this.spouse_nationality = user.spouse_nationality;
        this.spouse_birthplace = user.spouse_birthplace;
        this.spouse_occupation = user.spouse_occupation;
        this.spouse_unit = user.spouse_unit;
        this.spouse_allowance = user.spouse_allowance;
        this.spouse_phone = user.spouse_phone;

        this.siblings = user.siblings || [];
        this.children = user.children || [];

        // Relationships
        this.department = user.department;
        this.office = user.office;
        this.position = user.position;

        this.additional_positions = user.additional_positions || [];
        this.out_of_framework_statuses = user.out_of_framework_statuses || [];
        this.unpaid_leaves = user.unpaid_leaves || [];
        this.public_work_histories = user.public_work_histories || [];
        this.private_work_histories = user.private_work_histories || [];
        this.user_decorations = user.user_decorations || [];
        this.disciplinary_actions = user.disciplinary_actions || [];
        this.educations = user.educations || [];
        this.languages = user.languages || [];
        this.permissions = user.permissions || [];
      },

      resetState() {
        this.id = null;
        this.name = null;
        this.name_kh = null;
        this.name_en = null;
        this.email = null;
        this.profile_image = null;
        this.profile_thumbnail = null;
        this.password_null = true;
        this.level = null;
        this.status = null;
        this.employee_type = null;
        this.employee_code = null;
        this.mef_card_number = null;
        this.department_id = null;
        this.office_id = null;
        this.position_id = null;
        this.department = null;
        this.office = null;
        this.position = null;

        // Reset ព័ត៌មានផ្ទាល់ខ្លួនផ្សេងៗ
        this.gender = null;
        this.dob = null;
        this.marital_status = null;
        this.birth_place = null;
        this.current_address = null;
        this.phone = null;
        this.national_id_number = null;
        this.national_id_expired_date = null;
        this.national_id_file = null;
        this.passport_number = null;
        this.passport_expired_date = null;
        this.passport_file = null;

        // Reset ពេលចាកចេញ
        this.first_service_date = null;
        this.first_appointment_date = null;
        this.initial_framework = null;
        this.initial_position = null;
        this.initial_ministry = null;
        this.initial_unit = null;
        this.initial_department = null;
        this.initial_office = null;

        this.officer_status = 'ACTIVE';
        this.officer_status_date = null;
        this.officer_status_reason = null;
        this.service_duration = null;
        this.service_duration_formatted = null;
        this.current_framework = null;
        this.current_appointment_date = null;
        this.current_position_date = null;

        // ៧. ស្ថានភាពគ្រួសារ
        this.father_name = null;
        this.father_latin_name = null;
        this.father_status = null;
        this.father_dob = null;
        this.father_nationality = null;
        this.father_address = null;
        this.father_occupation = null;
        this.father_unit = null;

        this.mother_name = null;
        this.mother_latin_name = null;
        this.mother_status = null;
        this.mother_dob = null;
        this.mother_nationality = null;
        this.mother_address = null;
        this.mother_occupation = null;
        this.mother_unit = null;

        this.spouse_name = null;
        this.spouse_latin_name = null;
        this.spouse_status = null;
        this.spouse_dob = null;
        this.spouse_nationality = null;
        this.spouse_birthplace = null;
        this.spouse_occupation = null;
        this.spouse_unit = null;
        this.spouse_allowance = null;
        this.spouse_phone = null;

        this.siblings = [];
        this.children = [];

        this.additional_positions = [];
        this.out_of_framework_statuses = [];
        this.unpaid_leaves = [];
        this.public_work_histories = [];
        this.private_work_histories = [];
        this.user_decorations = [];
        this.disciplinary_actions = [];
        this.educations = [];
        this.languages = [];
        this.permissions = [];

        localStorage.removeItem("user_level");
      },

      // User Sanctum Token management
      setSanctumToken(token) {
        localStorage.setItem('SANCTUM-TOKEN', token);
      },
      getSanctumToken() {
        return localStorage.getItem('SANCTUM-TOKEN');
      },
      removeSanctumToken() {
        localStorage.removeItem('SANCTUM-TOKEN');
      },

      // Reset user state and remove Sanctum token (e.g., on sign out)
      reset() {
        this.resetState();
        this.removeSanctumToken();
      },
    },
    persist: true,
  }
);